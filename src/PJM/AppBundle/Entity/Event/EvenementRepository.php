<?php

namespace PJM\AppBundle\Entity\Event;

use Doctrine\ORM\EntityRepository;
use PJM\UserBundle\Entity\User;
use Doctrine\Common\Collections\Collection;

/**
 * EvenementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EvenementRepository extends EntityRepository
{
    public function getEvents(User $user, $max = 6, $quand = 'after', \DateTime $date = null)
    {
        if ($date == null) {
            $date = new \DateTime();
        }

        $qb = $this->createQueryBuilder('e')
            ->where('e.isPublic = true')
        ;

        if ($quand == 'after') {
            $qb
                ->andWhere('e.dateDebut > :date')
                ->orderBy('e.dateDebut', 'ASC')
            ;
        } else if ($quand == 'before') {
            $qb
                ->andWhere('e.dateDebut < :date')
                ->orderBy('e.dateDebut', 'DESC')
            ;
        }

        $qb->setParameter('date', $date);

        // TODO ajouter les évents privés dont l'user est participant

        if ($max !== null) {
            $qb
                ->setMaxResults($max)
            ;
        }

        $res = $qb->getQuery()->getResult();

        return $res;
    }
}
