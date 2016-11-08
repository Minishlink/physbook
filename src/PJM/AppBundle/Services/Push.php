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
     * @param ArrayCollection $users   Les utilisateurs destinataires.
     * @param string          $message Le message de la notification.
     */
    public function sendNotificationToUsers(ArrayCollection $users, $message)
    {
        // aller chercher tous les endpoints des Users
        $pushSubscriptionRepository = $this->em->getRepository('PJMAppBundle:PushSubscription');
        $subscriptions = $pushSubscriptionRepository->findByUsers($users);

        if (!$subscriptions) {
            return;
        }

        $payload = json_encode(array(
            'message' => $message,
        ));

        /** @var PushSubscription $subscription */
        foreach ($subscriptions as $subscription) {
            $this->webPush->sendNotification($subscription->getEndpoint(), $payload, $subscription->getUserPublicKey(), $subscription->getUserAuthToken());
        }

        $res = $this->webPush->flush();

        if (is_array($res)) {
            foreach ($res as $result) {
                if (!$result['success']) {
                    if ($result['expired']) {
                        $this->em->remove($pushSubscriptionRepository->findOneBy(array('endpoint' => $result['endpoint'])));
                    }
                }
            }
        }
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
