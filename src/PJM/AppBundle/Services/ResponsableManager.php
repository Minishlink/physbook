<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Responsabilite;
use PJM\AppBundle\Entity\Responsable;
use PJM\AppBundle\Entity\User;

class ResponsableManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(User $user, Responsabilite $responsabilite, $active = true, $flush = true)
    {
        $responsable = new Responsable();
        $responsable->setUser($user);
        $responsable->setResponsabilite($responsabilite);
        $responsable->setActive($active);

        $this->update($responsable, $flush);
    }

    public function update(Responsable $responsable, $flush = true)
    {
        $this->em->persist($responsable);

        if ($flush) {
            $this->em->flush();
        }
    }

    public function estNiveauUn(User $user, Boquette $boquette)
    {
        $respo = $this->getRepository()->estNiveauUn($user, $boquette);

        if ($respo !== null && $respo != array() || $user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    private function getRepository()
    {
        return $this->em->getRepository('PJMAppBundle:Responsable');
    }
}
