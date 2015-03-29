<?php

namespace PJM\AppBundle\Entity\Consos;

use Doctrine\ORM\EntityRepository;

/**
 * TransfertRepository
 */
class TransfertRepository extends EntityRepository
{
    public function findByUserAndBoquetteSlug(\PJM\UserBundle\Entity\User $user, $boquetteSlug, $limit = null)
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.receveur', 'r', 'WITH', 'r.user = :user')
            ->leftJoin('t.emetteur', 'e', 'WITH', 'e.user = :user')
            ->leftJoin('r.boquette', 'br', 'WITH', 'br.slug = :boquette_slug')
            ->leftJoin('e.boquette', 'be', 'WITH', 'be.slug = :boquette_slug')
            ->setParameter('user', $user)
            ->setParameter('boquette_slug', $boquetteSlug)
            ->orderBy('t.date', 'desc')
        ;

        if ($limit != null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}

