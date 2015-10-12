<?php

namespace PJM\AppBundle\Entity\Notifications;

use Doctrine\ORM\EntityRepository;
use PJM\AppBundle\Entity\User;

/**
 * NotificationRepository
 */
class NotificationRepository extends EntityRepository
{
    public function countNotReceived(User $user)
    {
        $qb = $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->where('n.user = :user')
            ->andWhere('n.received = false')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}
