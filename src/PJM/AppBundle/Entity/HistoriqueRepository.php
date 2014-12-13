<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
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

    public function callbackFindByBoquetteSlug($boquette_slug)
    {
        return function($qb) use($boquette_slug) {
            $qb
                ->join('Historique.item', 'i')
                ->join('i.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
                ->setParameters(array(
                    'boquette_slug'  => $boquette_slug,
                ))
            ;
        };
    }

    public function callbackFindByUser($user)
    {
        return function($qb) use($user) {
            $qb
                ->join('Historique.user', 'u', 'WITH', 'u = :user')
                ->setParameters(array(
                    'user'  => $user,
                ))
            ;
        };
    }
}
