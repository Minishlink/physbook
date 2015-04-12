<?php

namespace PJM\AppBundle\Entity\Media;

use Doctrine\ORM\EntityRepository;

/**
 * PhotoRepository
 */
class PhotoRepository extends EntityRepository
{
    public function getTotalHM($publication = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.usersHM', 'uHM')
            ->join('uHM.users', 'u')
            ->select('count(u.id) AS HM')
        ;

        if ($publication !== null) {
            $qb
                ->andWhere('p.publication = :publication')
                ->setParameter(':publication', $publication)
            ;
        }

        try {
            $res = $qb->getQuery()->getSingleScalarResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }
}
