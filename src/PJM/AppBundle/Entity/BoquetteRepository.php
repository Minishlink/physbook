<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BoquetteRepository
 */
class BoquetteRepository extends EntityRepository
{
    public function getAllExceptSlugs($slugs)
    {
        $qb = $this->createQueryBuilder('b');

        $qb
            ->andWhere('b.slug NOT IN(:slugs)')
            ->setParameter('slugs', $slugs)
            ->orderBy('b.nom');

        return $qb->getQuery()->getResult();
    }
}
