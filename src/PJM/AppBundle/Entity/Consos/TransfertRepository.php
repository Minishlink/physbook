<?php

namespace PJM\AppBundle\Entity\Consos;

use Doctrine\ORM\EntityRepository;

/**
 * TransfertRepository
 */
class TransfertRepository extends EntityRepository
{
    public function findByCompte(\PJM\AppBundle\Entity\Compte $compte, $limit = null)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.receveur = :compte')
            ->orWhere('t.emetteur = :compte')
            ->setParameter('compte', $compte)
            ->orderBy('t.date', 'desc')
        ;

        if ($limit != null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}

