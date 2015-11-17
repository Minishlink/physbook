<?php

namespace PJM\AppBundle\Services;

use Buzz\Browser;
use Buzz\Exception\RequestException;
use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Notifications\Notification;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Enum\Notifications\NotificationEnum;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

class NotificationManager
{
    private $em;
    private $requestStack;
    private $push;
    private $notificationsList;
    private $mailer;
    private $buzz;
    private $translator;

    public function __construct(EntityManager $em, RequestStack $requestStack, Push $push, Mailer $mailer, Browser $buzz, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->push = $push;
        $this->mailer = $mailer;
        $this->notificationsList = NotificationEnum::$list;
        $this->buzz = $buzz;
        $this->translator = $translator;
    }

    /**
     * @param $key
     * @param $infos
     * @param array|User $users
     * @param bool|true  $flush
     *
     * @return bool
     */
    public function send($key, $infos, $users, $flush = true)
    {
        $notificationType = isset($this->notificationsList[$key]) ? $this->notificationsList[$key] : null;

        // on vérifie que ce type de notification existe
        if (!isset($notificationType)) {
            return false;
        }

        // on vérifie qu'il y a les bonnes infos pour remplir le message
        foreach ($notificationType['infos'] as $k_infos) {
            if (!array_key_exists($k_infos, $infos))
                return false;
        }

        if ($users instanceof User) {
            $users = array($users);
        }

        /** @var User $user */
        foreach ($users as $user) {
            // on enregistre la notification en BDD
            $notification = new Notification();
            $notification->setKey($key);
            $notification->setInfos($infos);
            $user->addNotification($notification);

            // on regarde si l'utilisateur a plus de 50 notifications, si oui on supprime la première
            if ($this->count($user) > 50) {
                $user->removeNotification($this->em->getRepository('PJMAppBundle:Notifications\Notification')->getFirst($user));
            }

            $this->em->persist($user);

            // si l'utilisateur est abonné à ce type de notification, on envoit un push ou/et un webhook
            $settings = $user->getNotificationSettings();
            if ($settings->has($notificationType['type'])) {
                $message = $this->getMessage($notification);
                $this->sendPushToUser($user, $message);
                $this->sendToWebhook($settings->getWebhook(), $message);

                if ($settings->isEmail()) {
                    $this->sendToEmail($user->getEmail(), $message);
                }
            }
        }

        if ($flush) {
            $this->em->flush();
        }

        return true;
    }

    public function sendFlash($type, $message)
    {
        $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add($type, $message);
    }

    public function sendPushToUser(User $user, $message)
    {
        $this->push->sendNotificationToUser($user, $message);
    }

    public function sendToWebhook($webhook, $message)
    {
        if (empty($webhook)) {
            return false;
        }

        // format message
        $message = "[Phy'sbook] ".$message.' https://physbook.fr';

        $headers = array(
            'content-type' => 'text/plain; charset=utf-8',
        );

        try {
            $response = $this->buzz->post($webhook.$message, $headers);
        } catch (RequestException $e) {
            return false;
        }

        if ($response->getStatusCode() != 200) {
            $this->sendToEmail(
                'error@physbook.fr',
                'Erreur '.$response->getStatusCode().' lors de l\'accès au webhook "'.$webhook.'"."'
            );

            return false;
        }

        return true;
    }

    public function sendToEmail($email, $message)
    {
        $this->mailer->sendMessageToEmail($message, $email);
    }

    public function get(User $user)
    {
        $notifications = $user->getNotifications();
        $settings = $user->getNotificationSettings();

        $notifications = $notifications->map(function (Notification $notification) use ($settings) {
            // on remplace les variables par %infos%
            $infos = $notification->getInfos();
            $notification->setVariables($this->transformInfosToVariables($infos));

            // on indique comme non lue ou pas (pour pas que cela soit changé ensuite quand on marque comme lu)
            $notification->setNew(!$notification->getReceived());

            if (isset($this->notificationsList[$notification->getKey()])) {
                $notificationType = $this->notificationsList[$notification->getKey()];

                // on ajoute les infos non variables
                $notification->setTitre($notificationType['titre']);
                $notification->setType($notificationType['type']);
                $notification->setPath($notificationType['path']);

                // on vérifie que l'utilisateur est abonné ou non au type de notification, et si oui on indique "important"
                $notification->setImportant($settings->has($notificationType['type']));
            }

            if (array_key_exists('path', $infos)) {
                // si la notification a un custom path
                $notification->setPath($infos['path']);
            }

            if (array_key_exists('path_params', $infos)) {
                // si le path de la notification a des paramètres
                $notification->setPathParams($infos['path_params']);
            }

            return $notification;
        });

        return $notifications;
    }

    private function getMessage(Notification $notification, $strip = true)
    {
        // on remplace les variables par %infos%
        $infos = $this->transformInfosToVariables($notification->getInfos());

        $message = $this->translator->trans('notifications.content.'.$notification->getKey(), $infos);

        return $strip ? strip_tags($message) : $message;
    }

    private function transformInfosToVariables(array $infos)
    {
        $filter = array('path', 'path_params');

        $variables = array();
        foreach($infos as $k => $v) {
            if (in_array($k, $filter))
                continue;

            $variables['%'.$k.'%'] = $v;
        }

        return $variables;
    }

    /**
     * @param User $user
     */
    public function markAllAsRead(User $user)
    {
        $notifications = $this->em->getRepository("PJMAppBundle:Notifications\Notification")->findBy(array(
            'user' => $user,
            'received' => false,
        ));

        /** @var Notification $notification */
        foreach ($notifications as $notification) {
            $notification->setReceived(true);
            $this->em->persist($notification);
        }

        $this->em->flush();
    }

    public function count(User $user, $received = null)
    {
        return $this->em->getRepository('PJMAppBundle:Notifications\Notification')->count($user, $received);
    }

    public function getLastNotificationByPushEndpoint($endpoint)
    {
        // on va chercher l'user qui a cet endpoint
        $pushSubscription = $this->em->getRepository('PJMAppBundle:PushSubscription')->findOneBy(array(
            'endpoint' => $endpoint,
        ));

        if (empty($pushSubscription)) {
            return false;
        }

        $notification = $this->em->getRepository('PJMAppBundle:Notifications\Notification')->getLast($pushSubscription->getUser());

        if (empty($notification)) {
            return false;
        }

        return array(
            'message' => $this->getMessage($notification),
        );
    }
}
