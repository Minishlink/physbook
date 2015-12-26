<?php

namespace PJM\AppBundle\Services\Consos;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Boquette;

class CompteManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getComptesWithLessThan($balance, Boquette $boquette)
    {
        return $this->em->getRepository('PJMAppBundle:Compte')->findByBoquetteAndMaxSolde($boquette, $balance);
    }
}
