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

        if ($limit !== null) {
            $qb
                ->andWhere("t.status = 'OK'")
                ->setMaxResults($limit)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function callbackFindByUser($user)
    {
        return function($qb) use($user) {
            $qb
                ->join('Transfert.receveur', 'r')
                ->join('Transfert.emetteur', 'e')
                ->andWhere('r.user = :user OR e.user = :user')
                ->andWhere('Transfert.status IS NOT NULL')
                ->setParameter('user', $user)
            ;
        };
    }
}

