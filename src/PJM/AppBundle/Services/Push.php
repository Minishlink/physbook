<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use PJM\AppBundle\Entity\User;
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

    public function sendNotificationToUsers(ArrayCollection $users, $message)
    {
        foreach ($users as $user) {
            $this->sendNotificationToUser($user, $message);
        }
    }

    /**
     * Envoit une Notification Push à l'utilisateur.
     *
     * @param User $user L'utilisateur destinataire
     * @param string $message Le message "body" de la notification
     */
    public function sendNotificationToUser(User $user, $message)
    {
        // aller chercher tous les subscriptionId de l'utilisateur
        $subscriptions = $user->getPushSubscriptions();

        // on envoit une notif pour chaque
        if ($subscriptions !== null) {
            $dateLimite = new \DateTime('3 months ago');
            foreach ($subscriptions as $subscription) {
                // on vérifie que la subscription est pas trop vieille pour éviter d'exploser le quota
                if ($subscription->getLastSubscribed() > $dateLimite) {
                    $this->sendNotificationToSubscriptionId($subscription->getSubscriptionId(), $message);
                }
            }
        }
    }

    public function sendNotificationToSubscriptionId($subscriptionId, $message)
    {
        $notification = new AndroidMessage();
        $notification->setGCM(true);

        $notification->setMessage($message);
        $notification->setDeviceIdentifier($subscriptionId);

        $this->rms_push_notifications->send($notification);
    }
}
