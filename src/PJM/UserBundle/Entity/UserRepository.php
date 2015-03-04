<?php

namespace PJM\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    public function findByRole($role)
    {
        $query = $this->createQueryBuilder('u')
                ->where('u.roles LIKE :roles')
                ->setParameter('roles', '%"' . $role . '"%')
                ->getQuery();
        return $query->getResult();
    }

    public function getActive(User $excludedUser = null)
    {
        $delay = new \DateTime();
        $delay->setTimestamp(strtotime('2 minutes ago'));

        $qb = $this->createQueryBuilder('u')
            ->where('u.lastActivity > :delay')
            ->setParameter('delay', $delay)
        ;

        if ($excludedUser !== null) {
            $qb
                ->andWhere('u != :excludedUser')
                ->setParameter('excludedUser', $excludedUser)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function getOneActive(User $user)
    {
        $delay = new \DateTime();
        $delay->setTimestamp(strtotime('2 minutes ago'));

        $query = $this->createQueryBuilder('u')
            ->where('u = :user')
            ->andWhere('u.lastActivity > :delay')
            ->setParameter('delay', $delay)
            ->setParameter('user', $user)
            ->getQuery()
        ;

        try {
            $res = $query->getSingleResult();
            $res = true;
        } catch (\Doctrine\ORM\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function getByDateAnniversaire(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('MONTH(u.anniversaire) = :mois')
            ->andWhere('DAY(u.anniversaire) = :jour')
            ->setParameter('mois', $date->format('m'))
            ->setParameter('jour', $date->format('d'))
        ;

        return $qb->getQuery()->getResult();
    }
}
