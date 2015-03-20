<?php

namespace PJM\AppBundle\Entity\Inbox;

use Doctrine\ORM\EntityRepository;

/**
 * ReceptionRepository
 */
class ReceptionRepository extends EntityRepository
{
    public function getAnnoncesByInbox(Inbox $inbox, $lu = null, $number = null)
    {
        $qb = $this->createQueryBuilder('r')
                    ->where('r.inbox = :inbox')
                    ->join('r.message', 'm', 'WITH', 'm.isAnnonce = true')
                    ->setParameter(':inbox', $inbox)
                    ->orderBy('m.date', 'desc')
        ;

        if ($lu !== null) {
            $qb
                ->andWhere('r.lu = :lu')
                ->setParameter(':lu', $lu)
            ;
        }

        if ($number !== null) {
            $qb->setMaxResults($number);
        }

        try {
            $res = $qb->getQuery()->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }
}
