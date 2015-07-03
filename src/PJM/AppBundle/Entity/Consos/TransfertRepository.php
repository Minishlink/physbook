<?php

namespace PJM\AppBundle\Entity\Consos;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * TransfertRepository.
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
        return function (QueryBuilder $qb) use ($user) {
            $qb
                ->join('transfert.receveur', 'r')
                ->join('transfert.emetteur', 'e')
                ->andWhere('r.user = :user OR e.user = :user')
                ->andWhere('transfert.status IS NOT NULL')
                ->setParameter('user', $user)
            ;
        };
    }
}
