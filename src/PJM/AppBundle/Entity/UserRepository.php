<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

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

    public function getByDateAnniversaire(\DateTime $date, $closeProms = null)
    {
        $qb = $this->createQueryBuilder('u');

        if (isset($closeProms)) {
            $this->filterByCloseProms($qb, $closeProms);
        }

        $qb
            ->andWhere('MONTH(u.anniversaire) = :mois')
            ->andWhere('DAY(u.anniversaire) = :jour')
            ->setParameter('mois', $date->format('m'))
            ->setParameter('jour', $date->format('d'))
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $proms
     * @return array
     */
    public function getByProms(array $proms)
    {
        $qb = $this->createQueryBuilder('u');

        foreach ($proms as $promo) {
            $qb
                ->orWhere('u.proms = '.$promo) // can't set parameter
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function getByBirthdayBetweenDates(\DateTime $debut, \DateTime $fin, $closeProms = null)
    {
        $qb = $this->createQueryBuilder('u');

        if (isset($closeProms)) {
            $this->filterByCloseProms($qb, $closeProms);
        }

        $month_debut = $month = (int)$debut->format('m');
        $month_fin = (int)$fin->format('m');
        $months[] = $month_debut;
        while ($month !== $month_fin) {
            if(++$month === 13) {
                $month = 1;
            }

            $months[] = $month;
        }

        $qb
            ->andWhere('MONTH(u.anniversaire) IN (:months)')
            ->setParameter('months', $months)
        ;

        return $qb->getQuery()->getResult();
    }

    private function filterByCloseProms(QueryBuilder $qb, $proms) {
        $qb
            ->andWhere('u.proms >= :proms - 1')
            ->andWhere('u.proms <= :proms + 1')
            ->setParameter('proms', $proms)
        ;
    }

    public function findByNums($nums, $closeProms = null)
    {
        $qb = $this->createQueryBuilder('u');

        if (isset($closeProms)) {
            $this->filterByCloseProms($qb, $closeProms);
        }

        $qb
            ->andWhere('u.nums LIKE :nums')
            ->setParameter('nums', '%'.$nums.'%')
        ;

        return $qb->getQuery()->getResult();
    }
}
