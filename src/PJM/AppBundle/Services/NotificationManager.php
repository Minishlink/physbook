<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Notifications\Notification;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Enum\Notifications\NotificationEnum;
use Symfony\Component\HttpFoundation\RequestStack;

class NotificationManager
{
    private $em;
    private $requestStack;
    private $push;
    private $notificationsList;

    public function __construct(EntityManager $em, RequestStack $requestStack, Push $push)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->push = $push;
        $this->notificationsList = NotificationEnum::$list;
    }

    public function send($key, $infos, User $user, $flush = true)
    {
        $notificationType = isset($this->notificationsList[$key]) ? $this->notificationsList[$key] : null;

        // on vérifie que ce type de notification existe
        if (!isset($notificationType)) {
            return false;
        }

        // on vérifie qu'il y a les bonnes infos pour remplir le message
        if ($notificationType['infos'] != array_keys($infos)) {
            return false;
        }

        // on enregistre la notification en BDD
        $notification = new Notification();
        $notification->setKey($key);
        $notification->setInfos($infos);
        $user->addNotification($notification);

        // on regarde si l'utilisateur a plus de 50 notifications, si oui on supprime la première
        if (count($user->getNotifications()) > 50) {
            $user->removeNotification($user->getNotifications()->first());
        }

        $this->em->persist($user);

        // TODO si l'utilisateur est abonné à ce type de notification, on envoit un push
        // si notificationType['type'] dans tableau des notifications abonnées de l'utilisateur, on envoit

        if ($flush) {
            $this->em->flush();
        }

        return true;
    }

    public function sendFlash($type, $message)
    {
        $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add($type, $message);
    }

    public function sendPushToUser(User $user, $message, $type)
    {
        $this->push->sendNotificationToUser($user, $message, $type);
    }

    public function get(User $user)
    {
        $notifications = $user->getNotifications();

        $notifications = $notifications->map(function(Notification $notification) {
            // on remplace les infos par %infos%
            $infos = $notification->getInfos();

            $newKeys = array_map(function($k) {
                return "%".$k."%";
            }, array_keys($infos));

            $notification->setInfos(array_combine(
                $newKeys,
                array_values($infos)
            ));

            // on ajoute le type et le path
            $notificationType = $this->notificationsList[$notification->getKey()];

            $notification->setType($notificationType['type']);
            $notification->setPath($notificationType['path']);

            // TODO on vérifie que l'utilisateur est abonné ou non au type de notification, et si oui on indique "important"

            return $notification;
        });

        return $notifications;
    }
}
