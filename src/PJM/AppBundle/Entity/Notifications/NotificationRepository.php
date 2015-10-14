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

    /**
     * @param User $user
     * @return null|Notification
     */
    public function getFirst(User $user)
    {
        return $this->findOneBy(array(
            'user' => $user
        ), array('date' => 'asc'));
    }

    /**
     * @param User $user
     * @return null|Notification
     */
    public function getLast(User $user)
    {
        return $this->findOneBy(array(
            'user' => $user
        ), array('date' => 'desc'));
    }
}
