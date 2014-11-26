<?php

namespace PJM\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    public function findByRole($role)
    {
        $query = $this->createQueryBuilder('u')
                ->where('u.roles LIKE :roles')
                ->setParameter('roles', '%"' . $role . '"%')
                ->getQuery();
        return $query->getResult();
    }
}
