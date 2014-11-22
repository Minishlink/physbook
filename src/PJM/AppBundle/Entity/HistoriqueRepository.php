<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PJM\UserBundle\Entity\User;

/**
 * HistoriqueRepository
 */
class HistoriqueRepository extends EntityRepository
{
    public function findAllByUserAndItem(User $user, Item $item)
    {
        $query = $this->createQueryBuilder('h')
                    ->where('h.user = :user')
                    ->andWhere('h.item = :item')
                    ->setParameters(array(
                        'user' => $user,
                        'item'  => $item,
                    ))
                    ->orderBy('h.date', 'desc')
                    ->getQuery();

        try {
            $res = $query->getResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $res = null;
        }

        return $res;
    }
}
