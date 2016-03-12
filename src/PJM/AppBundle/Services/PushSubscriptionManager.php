<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\PushSubscription;
use PJM\AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validation;

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
     * @param string $endpoint
     * @param string $key User public key
     *
     * @return null|PushSubscription
     */
    public function create(User $user, $endpoint, $key)
    {
        $pushSubscription = new PushSubscription();
        $pushSubscription
            ->setEndpoint($endpoint)
            ->setUserPublicKey($key)
            ->setUser($user)
        ;

        return $this->update($user, $pushSubscription);
    }

    /**
     * @param string $endpoint
     *
     * @return null|PushSubscription
     */
    public function find($endpoint)
    {
        return $this->em->getRepository('PJMAppBundle:PushSubscription')->findOneBy(array('endpoint' => $endpoint));
    }

    /**
     * @param User             $user
     * @param PushSubscription $pushSubscription
     *
     * @return null|PushSubscription
     */
    public function update(User $user, PushSubscription $pushSubscription)
    {
        if (!$this->canAccess($user, $pushSubscription)) {
            return;
        }

        $pushSubscription->refreshLastSubscribed();
        $pushSubscription->setBrowserUA($this->requestStack->getCurrentRequest()->server->get('HTTP_USER_AGENT', 'Unknown'));

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        if ($validator->validate($pushSubscription)->count()) {
            return;
        }

        $this->persist($pushSubscription);

        return $pushSubscription;
    }

    /**
     * @param User             $user
     * @param PushSubscription $pushSubscription
     *
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
     * @param User             $user
     * @param PushSubscription $pushSubscription
     *
     * @return bool
     */
    private function canAccess(User $user, PushSubscription $pushSubscription)
    {
        return $pushSubscription->getUser() === $user;
    }

    /**
     * @param PushSubscription $pushSubscription
     * @param bool|true        $flush
     */
    private function persist(PushSubscription $pushSubscription, $flush = true)
    {
        $this->em->persist($pushSubscription);

        if ($flush) {
            $this->em->flush();
        }
    }
}
