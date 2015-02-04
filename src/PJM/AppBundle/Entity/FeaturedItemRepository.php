<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * FeaturedItemRepository
 */
class FeaturedItemRepository extends EntityRepository
{
    public function findByBoquetteSlug($boquetteSlug, $active = null, $item_valid = null)
    {
        $qb = $this->createQueryBuilder('FeaturedItem');
        $callback = $this->callbackFindByBoquetteSlug($boquetteSlug, $active, $item_valid);
        $callback($qb);

        try {
            $res = $qb->getQuery()->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function callbackFindByBoquetteSlug($boquette_slug, $active = null, $item_valid = null)
    {
        return function($qb) use($boquette_slug, $active, $item_valid) {
            $qb
                ->join('FeaturedItem.item', 'i')
                ->join('i.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
                ->setParameter('boquette_slug', $boquette_slug)
                ->orderBy('FeaturedItem.date', 'desc')
            ;

            if (isset($active)) {
                $qb
                    ->andWhere('FeaturedItem.active = :active')
                    ->setParameter('active', $active)
                ;
            }

            if (isset($item_valid)) {
                $qb
                    ->andWhere('i.valid = :item_valid')
                    ->setParameter('item_valid', $item_valid)
                ;
            }
        };
    }
}
