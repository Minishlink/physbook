<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use PJM\UserBundle\Entity\User;

/**
 * HistoriqueRepository
 */
class HistoriqueRepository extends EntityRepository
{
    public function findByUserAndItemSlug(User $user, $item_slug)
    {
        $query = $this->createQueryBuilder('h')
                    ->where('h.user = :user')
                    ->join('h.item', 'i', 'WITH', 'i.slug = :item_slug')
                    ->setParameters(array(
                        'user' => $user,
                        'item_slug'  => $item_slug,
                    ))
                    ->orderBy('h.date', 'desc')
                    ->getQuery();

        try {
            $res = $query->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function findByUserAndItem(User $user, Item $item)
    {
        $query = $this->createQueryBuilder('h')
                    ->where('h.user = :user')
                    ->andWhere('h.item = :item')
                    ->setParameters(array(
                        'user' => $user,
                        'item'  => $item,
                    ))
                    ->orderBy('h.date', 'desc')
                    ->getQuery();

        try {
            $res = $query->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function findByItemSlug($item_slug)
    {
        $query = $this->createQueryBuilder('h')
                    ->join('h.item', 'i', 'WITH', 'i.slug = :item_slug')
                    ->setParameters(array(
                        'item_slug'  => $item_slug,
                    ))
                    ->orderBy('h.date', 'desc')
                    ->getQuery();

        try {
            $res = $query->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function findByItem($item, $valid = true, $orderByUser = false)
    {
        $qb = $this->createQueryBuilder('h')
            ->where('h.item = :item')
            ->setParameters(array(
                'item'  => $item,
            ))
        ;

        if (!isset($valid) || $valid) {
            $qb->andWhere('h.valid = true');
        }

        if ($orderByUser) {
            $qb
                ->join('h.user', 'u')
                ->addOrderBy('u.proms', 'asc')
                ->addOrderBy('u.fams', 'desc')
            ;
        } else {
            $qb->orderBy('h.date', 'desc');
        }

        $query = $qb->getQuery();

        try {
            $res = $query->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function findLastValidByItemSlug($item_slug)
    {
        $query = $this->createQueryBuilder('h')
                    ->where('h.valid = true')
                    ->join('h.item', 'i', 'WITH', 'i.slug = :item_slug')
                    ->setParameters(array(
                        'item_slug'  => $item_slug,
                    ))
                    ->orderBy('h.date', 'desc')
                    ->setMaxResults(1)
                    ->getQuery();

        try {
            $res = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function findLastValidByBoquetteSlug($boquetteSlug)
    {
        $query = $this->createQueryBuilder('h')
                    ->where('h.valid = true')
                    ->join('h.item', 'i')
                    ->join('i.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
                    ->setParameter('boquette_slug', $boquetteSlug)
                    ->orderBy('h.date', 'desc')
                    ->addOrderBy('h.id', 'desc')
                    ->setMaxResults(1)
                    ->getQuery();

        try {
            $res = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function findByUserAndBoquetteSlug(User $user, $boquetteSlug, $limit = null, $valid = null)
    {
        $qb = $this->createQueryBuilder('h')
            ->where('h.user = :user')
            ->join('h.item', 'i')
            ->join('i.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
            ->setParameters(array(
                'user' => $user,
                'boquette_slug'  => $boquetteSlug
            ))
            ->orderBy('h.date', 'desc')
        ;

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($valid !== null) {
            $qb
                ->andWhere('h.valid = :valid')
                ->setParameter('valid', $valid)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function countByItemSlug($item, $month, $year)
    {
        $qb = $this->createQueryBuilder('h')
            ->select('sum(h.nombre)')
            ->join('h.item', 'i', 'WITH', 'i.slug = :item OR i.libelle = :item')
            ->setParameters(array(
                'item'  => $item
            ))
        ;

        $qb = $this->triParDate($qb, $month, $year);

        return $qb->getQuery()->getSingleScalarResult()/10;
    }

    public function countByBoquetteSlug($boquetteSlug, $month, $year)
    {
        $qb = $this->createQueryBuilder('h')
            ->select('sum(h.nombre)')
            ->join('h.item', 'i')
            ->join('i.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
            ->setParameters(array(
                'boquette_slug'  => $boquetteSlug
            ))
        ;

        $qb = $this->triParDate($qb, $month, $year);

        return $qb->getQuery()->getSingleScalarResult()/10;
    }

    public function getTopUsers($boquetteSlug, $limit = null, $month = null, $year = null)
    {
        $qb = $this->createQueryBuilder('h')
            ->addSelect('SUM(h.nombre) AS somme')
            ->addSelect('u')
            ->join('h.user', 'u')
            ->join('h.item', 'i')
            ->join('i.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
            ->groupBy('u')
            ->orderBy('somme', 'desc')
            ->setParameters(array(
                'boquette_slug'  => $boquetteSlug
            ))
        ;

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        $qb = $this->triParDate($qb, $month, $year);

        return $qb->getQuery()->getResult();
    }

    public function triParDate($qb, $month, $year)
    {
        if ($month !== null) {
            if ($year === null) {
                $year = date('Y');
            }

            $qb
                ->where('h.date BETWEEN :debut AND :fin')
                ->setParameter('debut', $year.'-'.$month.'-01')
                ->setParameter('fin', $year.'-'.$month.'-31')
            ;
        } else if ($year !== null) {
            $qb
                ->where('h.date BETWEEN :debut AND :fin')
                ->setParameter('debut', $year.'-01-01')
                ->setParameter('fin', $year.'-12-31')
            ;
        }

        return $qb;
    }

    public function findByBoquetteSlug($boquette_slug)
    {
        $qb = $this->createQueryBuilder('Historique')
            ->join('Historique.item', 'i')
            ->join('i.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
            ->setParameter('boquette_slug', $boquette_slug)
        ;

        return $qb->getQuery()->getResult();
    }

    public function callbackFindByBoquetteSlug($boquette_slug)
    {
        return function(QueryBuilder $qb) use($boquette_slug) {
            $qb
                ->join('Historique.item', 'i')
                ->join('i.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
                ->setParameter('boquette_slug', $boquette_slug)
            ;
        };
    }

    public function callbackFindByUser($user)
    {
        return function(QueryBuilder $qb) use($user) {
            $qb
                ->join('Historique.user', 'u', 'WITH', 'u = :user')
                ->setParameter('user', $user)
            ;
        };
    }
}
