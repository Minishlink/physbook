<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Minishlink\WebPush\WebPush;
use PJM\AppBundle\Entity\PushSubscription;
use PJM\AppBundle\Entity\User;

class Push
{
    protected $em;
    protected $webPush;

    public function __construct(EntityManager $em, WebPush $webPush)
    {
        $this->em = $em;
        $this->webPush = $webPush;
    }

    /**
     * Envoit des notifications Push aux utilisateurs.
     *
     * @param ArrayCollection $users Les utilisateurs destinataires.
     * @param string $payload Le message de la notification.
     */
    public function sendNotificationToUsers(ArrayCollection $users, $payload)
    {
        // aller chercher tous les endpoints des Users en filtrant les vieilles subscriptions
        $subscriptions = $this->em->getRepository('PJMAppBundle:PushSubscription')->findByUsers($users, new \DateTime('3 months ago'));

        if (!$subscriptions) {
            return;
        }

        $endpoints = array();
        $payloads = array();
        $userPublicKeys = array();
        /** @var PushSubscription $subscription */
        foreach ($subscriptions as $subscription) {
            $endpoints[] = $subscription->getEndpoint();
            $payloads[] = $payload;
            $userPublicKeys[] = '';
        }

        $this->webPush->sendNotifications($endpoints, $payloads, $userPublicKeys);
    }

    /**
     * Envoit une Notification Push à l'utilisateur.
     *
     * @param User   $user    L'utilisateur destinataire
     * @param string $payload Le message de la notification
     */
    public function sendNotificationToUser(User $user, $payload)
    {
        $this->sendNotificationToUsers(new ArrayCollection(array($user)), $payload);
    }
}
