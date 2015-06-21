<?php

namespace PJM\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository.
 */
class UserRepository extends EntityRepository
{
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // default order
        $orderBy = $orderBy === null ? $this->defaultOrder() : $orderBy;

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    public function defaultOrder($qb = null, $alias = '')
    {
        if ($qb !== null) {
            foreach ($this->defaultOrder(null, 'u') as $order => $sort) {
                return $qb->orderBy($order, $sort);
            }

            return $qb;
        }

        if ($alias != '') {
            $alias .= '.';
        }

        return array($alias.'fams' => 'asc', $alias.'proms' => 'desc');
    }

    public function findByRole($role)
    {
        $query = $this->createQueryBuilder('u')
                ->where('u.roles LIKE :roles')
                ->setParameter('roles', '%"'.$role.'"%')
                ->getQuery();

        return $query->getResult();
    }

    public function getQbAllButUser(User $user)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u != :user')
            ->setParameter('user', $user)
        ;

        $this->defaultOrder($qb);

        return $qb;
    }

    public function getActive(User $excludedUser = null)
    {
        $delay = new \DateTime();
        $delay->setTimestamp(strtotime('5 minutes ago'));

        $qb = $this->createQueryBuilder('u')
            ->where('u.lastActivity > :delay')
            ->setParameter('delay', $delay)
            ->orderBy('u.lastActivity', 'desc')
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
        $delay->setTimestamp(strtotime('5 minutes ago'));

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
