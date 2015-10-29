<?php

namespace PJM\AppBundle\Entity\Event;

use Doctrine\ORM\EntityRepository;

/**
 * InvitationRepository.
 */
class InvitationRepository extends EntityRepository
{
    public function countParticipations(Evenement $event)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
            ->select('count(i.id)')
            ->where('i.estPresent = true')
            ->andWhere('i.event = :event')
            ->setParameter('event', $event)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}
