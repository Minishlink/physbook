<?php

namespace PJM\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * PushSubscriptionRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PushSubscriptionRepository extends EntityRepository
{
    public function callbackFindByUser(User $user)
    {
        return function (QueryBuilder $qb) use ($user) {
            $qb
                ->join('pushsubscription.user', 'u', 'WITH', 'u = :user')
                ->setParameter('user', $user)
            ;
        };
    }

    /**
     * @param ArrayCollection $users
     *
     * @return array|null
     */
    public function findByUsers(ArrayCollection $users)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.user IN (:users)')
            ->setParameter('users', $users);

        try {
            $res = $qb->getQuery()->getResult();
        } catch (NoResultException $e) {
            $res = null;
        }

        return $res;
    }
}
