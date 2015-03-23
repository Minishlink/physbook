<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ResponsableRepository
 */
class ResponsableRepository extends EntityRepository
{
    public function findByBoquette(Boquette $boquette, $active = true)
    {
        $query = $this->createQueryBuilder('r')
                    ->where('r.active = :active')
                    ->join('r.responsabilite', 're', 'WITH', 're.boquette = :boquette')
                    ->join('r.user', 'u')
                    ->setParameter('boquette', $boquette)
                    ->setParameter('active', $active)
                    ->addOrderBy('re.niveau', 'asc')
                    ->addOrderBy('u.bucque', 'asc')
                    ->getQuery();

        try {
            $res = $query->getResult();
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
