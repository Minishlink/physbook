<?php

namespace PJM\AppBundle\Entity\Notifications;

use Doctrine\ORM\EntityRepository;
use PJM\AppBundle\Entity\User;

/**
 * NotificationRepository
 */
class NotificationRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param $received
     * @return mixed
     */
    public function count(User $user, $received)
    {
        $qb = $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->where('n.user = :user')
            ->setParameter('user', $user)
        ;

        if ($received !== null) {
            $qb
                ->andWhere('n.received = :received')
                ->setParameter('received', $received)
            ;
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getFirst(User $user)
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.date', 'asc')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getSingleResult();
    }
}
