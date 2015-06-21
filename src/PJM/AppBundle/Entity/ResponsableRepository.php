<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ResponsableRepository.
 */
class ResponsableRepository extends EntityRepository
{
    public function findByBoquette(Boquette $boquette, $active = true)
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.responsabilite', 're', 'WITH', 're.boquette = :boquette')
            ->join('r.user', 'u')
            ->setParameter('boquette', $boquette)
            ->addOrderBy('re.niveau', 'asc')
            ->addOrderBy('u.bucque', 'asc')
        ;

        if ($active !== null) {
            $qb
                ->where('r.active = :active')
                ->setParameter('active', $active)
            ;
        }

        try {
            $res = $qb->getQuery()->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }

    public function estNiveauUn(\PJM\UserBundle\Entity\User $user, Boquette $boquette)
    {
        $query = $this->createQueryBuilder('r')
                    ->where('r.active = true')
                    ->join('r.responsabilite', 're', 'WITH', 're.boquette = :boquette AND re.niveau <= 1')
                    ->andWhere('r.user = :user')
                    ->setParameter('boquette', $boquette)
                    ->setParameter('user', $user)
                    ->addOrderBy('re.niveau', 'asc')
                    ->getQuery();

        try {
            $res = $query->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }
}
