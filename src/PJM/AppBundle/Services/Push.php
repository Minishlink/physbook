<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use PJM\UserBundle\Entity\User;
use PJM\AppBundle\Entity\PushSubscription;

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

    public function sendNotificationToUser(User $user, $message)
    {
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
