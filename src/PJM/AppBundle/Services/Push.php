<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use PJM\UserBundle\Entity\User;

use RMS\PushNotificationsBundle\Message\AndroidMessage;
use RMS\PushNotificationsBundle\Service\Notifications;

class Push
{
    protected $em;
    protected $rms_push_notifications;

    public function __construct(EntityManager $em, Notifications $rms_push_notifications)
    {
        $this->em = $em;
        $this->rms_push_notifications = $rms_push_notifications;
    }

    public function sendNotificationToUsers(ArrayCollection $users, $message, $type = null)
    {
        foreach ($users as $user) {
            $this->sendNotificationToUser($user, $message, $type);
        }
    }

    /**
     * Envoit une Notification Push à l'utilisateur en vérifiant que l'utilisateur a accepté ce type de notification
     * @param object User     $user L'utilisateur destinataire
     * @param string $message Le message "body" de la notification
     * @param string $type    Le type de notification
     */
    public function sendNotificationToUser(User $user, $message, $type = null)
    {
        if ($type !== null) {
            // on vérifie que l'utilisateur accepte ce type de notification
            $reglages = $user->getReglagesNotifications();
            if (!$reglages->has($type)) {
                return;
            }
        }

        // aller chercher tous les subscriptionId de l''utilisateur
        $subscriptions = $user->getPushSubscriptions();

        // on envoit une notif pour chaque
        if ($subscriptions != null) {
            foreach ($subscriptions as $subscription) {
                // TODO vérifier que la subscription est pas trop vieille pour éviter d'exploser le quota
                $this->sendNotificationToSubscriptionId($subscription->getSubscriptionId(), $message);
            }
        }
    }

    public function sendNotificationToSubscriptionId($subscriptionId, $message)
    {
        $message = new AndroidMessage();
        $message->setGCM(true);

        $message->setMessage($message);
        $message->setDeviceIdentifier($subscriptionId);

        $this->rms_push_notifications->send($message);
    }
}
