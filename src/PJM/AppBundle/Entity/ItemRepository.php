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

    public function callbackFindBySlug($slug)
    {
        return function($qb) use($slug) {
            $qb
                ->andWhere('Item.slug = :slug')
                ->setParameters(array(
                    'slug'  => $slug,
                ))
            ;
        };
    }
}
