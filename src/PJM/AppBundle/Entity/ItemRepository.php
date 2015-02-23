<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ItemRepository
 */
class ItemRepository extends EntityRepository
{
    public function findBySlugRegex($item_slug_regex)
    {
        $query = $this->createQueryBuilder('i')

                    ->where('REGEXP(i.slug, :item_slug_regex) = 1')
                    ->setParameters(array(
                        'item_slug_regex'  => $item_slug_regex,
                    ))
                    ->orderBy('i.date', 'desc')
                    ->getQuery();

        try {
            $res = $query->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function findOneBySlugAndValid($slug, $valid)
    {
        $query = $this->createQueryBuilder('i')
                    ->where('i.slug = :slug')
                    ->andWhere('i.valid = :valid')
                    ->setParameters(array(
                        'slug'  => $slug,
                        'valid'  => $valid,
                    ))
                    ->orderBy('i.date', 'desc')
                    ->getQuery();

        try {
            $res = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function findLastOneBySlugAndValid($slug, $valid = true)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.slug = :slug')
            ->setParameter('slug', $slug)
            ->orderBy('i.date', 'desc')
            ->setMaxResults(1)
        ;

        if (isset($valid)) {
            $qb
                ->andWhere('i.valid = :valid')
                ->setParameter('valid', $valid)
            ;
        }

        $query = $qb->getQuery();

        try {
            $res = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function callbackFindBySlug($slug)
    {
        return function($qb) use($slug) {
            $qb
                ->andWhere('Item.slug = :slug')
                ->setParameter('slug', $slug)
            ;
        };
    }

    public function findByBoquetteSlug($boquette_slug, $valid = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('i')
            ->join('i.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
            ->setParameter('boquette_slug', $boquette_slug)
            ->orderBy('i.date', 'desc')
        ;

        if (isset($valid)) {
            $qb
                ->andWhere('i.valid = :valid')
                ->setParameter('valid', $valid)
            ;
        }

        if (isset($limit)) {
            $qb
                ->setMaxResults($limit)
            ;
        }

        if (isset($offset)) {
            $qb
                ->setFirstResult($offset)
            ;
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function callbackFindByBoquetteSlug($boquette_slug)
    {
        return function($qb) use($boquette_slug) {
            $qb
                ->join('Item.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
                ->setParameter('boquette_slug', $boquette_slug)
            ;
        };
    }
}
