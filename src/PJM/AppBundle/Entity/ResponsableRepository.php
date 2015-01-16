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
                    ->orderBy('r.date', 'asc')
                    ->orderBy('u.username', 'asc')
                    ->getQuery();

        try {
            $res = $query->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }
}
