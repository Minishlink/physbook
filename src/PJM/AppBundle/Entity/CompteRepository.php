<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PJM\UserBundle\Entity\User;
use PJM\AppBundle\Entity\Boquette;

/**
 * CompteRepository
 */
class CompteRepository extends EntityRepository
{
    public function findOneByUserAndBoquette(User $user, Boquette $boquette)
    {
        $query = $this->createQueryBuilder('c')
                    ->where('c.user = :user')
                    ->andWhere('c.boquette = :boquette')
                    ->setParameters(array(
                        'user' => $user,
                        'boquette'  => $boquette,
                    ))
                    ->getQuery();

        try {
            $compte = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $compte = null;
        }

        return $compte;
    }

    public function findOneByUserAndBoquetteSlug(User $user, $boquetteSlug)
    {
        $query = $this->createQueryBuilder('c')
                    ->where('c.user = :user')
                    ->join('c.boquette', 'b', 'WITH', 'b.slug = :boquetteSlug')
                    ->setParameters(array(
                        'user' => $user,
                        'boquetteSlug'  => $boquetteSlug,
                    ))
                    ->getQuery();
        try {
            $compte = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $compte = null;
        }

        return $compte;
    }
}
