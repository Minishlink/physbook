<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * CompteRepository.
 */
class CompteRepository extends EntityRepository
{
    public function findOneByUserAndBoquetteSlug(User $user, $boquetteSlug)
    {
        if ($boquetteSlug == 'cvis') {
            $boquetteSlug = 'pians';
        }

        $query = $this->createQueryBuilder('c')
                    ->where('c.user = :user')
                    ->join('c.boquette', 'b', 'WITH', 'b.slug = :boquetteSlug')
                    ->setParameters(array(
                        'user' => $user,
                        'boquetteSlug' => $boquetteSlug,
                    ))
                    ->getQuery();
        try {
            $compte = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $compte = null;
        }

        return $compte;
    }

    public function findByBoquetteSlug($boquetteSlug)
    {
        if ($boquetteSlug == 'cvis') {
            $boquetteSlug = 'pians';
        }

        $query = $this->createQueryBuilder('c')
                    ->join('c.boquette', 'b', 'WITH', 'b.slug = :boquetteSlug')
                    ->setParameters(array(
                        'boquetteSlug' => $boquetteSlug,
                    ))
                    ->getQuery();
        try {
            $comptes = $query->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $comptes = null;
        }

        return $comptes;
    }

    public function findOneByUsernameAndBoquetteSlug($username, $boquetteSlug)
    {
        if ($boquetteSlug == 'cvis') {
            $boquetteSlug = 'pians';
        }

        $query = $this->createQueryBuilder('c')
                    ->join('c.boquette', 'b', 'WITH', 'b.slug = :boquetteSlug')
                    ->join('c.user', 'u', 'WITH', 'u.username = :username')
                    ->setParameters(array(
                        'username' => $username,
                        'boquetteSlug' => $boquetteSlug,
                    ))
                    ->getQuery();
        try {
            $compte = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $compte = null;
        }

        return $compte;
    }

    // solde >=
    public function findOneByUserAndBoquetteAndMinSolde(User $user, Boquette $boquette, $solde)
    {
        $query = $this->createQueryBuilder('c')
                    ->where('c.user = :user')
                    ->andWhere('c.boquette = :boquette')
                    ->andWhere('c.solde >= :solde')
                    ->setParameters(array(
                        'user' => $user,
                        'boquette' => $boquette,
                        'solde' => $solde,
                    ))
                    ->getQuery();
        try {
            $compte = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $compte = null;
        }

        return $compte;
    }

    // solde <
    public function findOneByUserAndBoquetteAndMaxSolde(User $user, Boquette $boquette, $solde)
    {
        $query = $this->createQueryBuilder('c')
                    ->where('c.user = :user')
                    ->andWhere('c.boquette = :boquette')
                    ->andWhere('c.solde < :solde')
                    ->setParameters(array(
                        'user' => $user,
                        'boquette' => $boquette,
                        'solde' => $solde,
                    ))
                    ->getQuery();
        try {
            $compte = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $compte = null;
        }

        return $compte;
    }

    public function findByBoquetteAndMaxSolde(Boquette $boquette, $solde)
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.boquette = :boquette')
            ->andWhere('c.solde < :solde')
            ->setParameters(array(
                'boquette' => $boquette,
                'solde' => $solde,
            ));

        return $qb->getQuery()->getResult();
    }

    public function buildFindByBoquette(Boquette $boquette)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.boquette = :boquette')
            ->setParameter('boquette', $boquette)
            ->orderBy('c.solde', 'desc')
        ;

        return $qb;
    }

    public function callbackFindByBoquetteSlug($slug)
    {
        return function (QueryBuilder $qb) use ($slug) {
            $qb
                ->join('compte.boquette', 'b', 'WITH', 'b.slug = :slug')
                ->setParameter('slug', $slug)
            ;
        };
    }

    public function callbackFindByUserAndBoquetteSlug(User $user, $slug)
    {
        return function (QueryBuilder $qb) use ($slug, $user) {
            $qb
                ->join('compte.boquette', 'b', 'WITH', 'b.slug = :slug')
                ->join('compte.user', 'u', 'WITH', 'u = :user')
                ->setParameter('slug', $slug)
                ->setParameter('user', $user)
            ;
        };
    }
}
