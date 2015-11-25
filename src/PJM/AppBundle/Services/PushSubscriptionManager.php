<?php

namespace PJM\AppBundle\Services;


use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\PushSubscription;
use PJM\AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

class PushSubscriptionManager
{
    protected $em;
    protected $requestStack;

    public function __construct(EntityManager $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    /**
     * @param User $user
     * @param $endpoint
     * @return null|PushSubscription
     */
    public function create(User $user, $endpoint)
    {
        $pushSubscription = new PushSubscription();
        $pushSubscription
            ->setEndpoint($endpoint)
            ->setUser($user)
        ;

        return $this->update($user, $pushSubscription);
    }

    /**
     * @param User $user
     * @param PushSubscription $pushSubscription
     * @return null|PushSubscription
     */
    public function update(User $user, PushSubscription $pushSubscription)
    {
        if (!$this->canAccess($user, $pushSubscription)) {
            return null;
        }

        $pushSubscription->refreshLastSubscribed();
        $pushSubscription->setBrowserUA($this->requestStack->getCurrentRequest()->server->get('HTTP_USER_AGENT', 'Unknown'));

        $this->persist($pushSubscription);

        return $pushSubscription;
    }

    /**
     * @param User $user
     * @param PushSubscription $pushSubscription
     * @return bool Success
     */
    public function delete(User $user, PushSubscription $pushSubscription)
    {
        if (!$this->canAccess($user, $pushSubscription)) {
            return false;
        }

        $this->em->remove($pushSubscription);
        $this->em->flush();

        return true;
    }

    /**
     * @param User $user
     * @param PushSubscription $pushSubscription
     * @return bool
     */
    private function canAccess(User $user, PushSubscription $pushSubscription)
    {
        return $pushSubscription->getUser() === $user;
    }

    /**
     * @param PushSubscription $pushSubscription
     * @param bool|true $flush
     */
    private function persist(PushSubscription $pushSubscription, $flush = true)
    {
        $this->em->persist($pushSubscription);

        if($flush) {
            $this->em->flush();
        }
    }
}