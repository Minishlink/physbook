<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * FeaturedItemRepository
 */
class FeaturedItemRepository extends EntityRepository
{
    public function findByBoquetteSlug($boquetteSlug, $item_valid = null)
    {
        $qb = $this->createQueryBuilder('f');
        $this->callbackFindByBoquetteSlug();

        try {
            $res = $qb->getQuery()->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }
    }

    public function callbackFindByBoquetteSlug($boquette_slug, $item_valid = null)
    {
        return function($qb) use($boquette_slug, $item_valid) {
            $qb
                ->join('FeaturedItem.item', 'i')
                ->join('i.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
                ->setParameter('boquette_slug', $boquette_slug)
                ->orderBy('FeaturedItem.date', 'desc')
            ;

            if (isset($item_valid)) {
                $qb
                    ->andWhere('i.valid = :valid')
                    ->setParameter('valid', $item_valid)
                ;
            }
        };
    }
}
