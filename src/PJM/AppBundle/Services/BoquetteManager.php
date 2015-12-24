<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\User;

class BoquetteManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get all boquettes
     * @param bool $withSpecial If true, includes special boquettes (pians, cvis, brags, paniers)
     *
     * @return array|\PJM\AppBundle\Entity\Boquette[]
     */
    public function getAll($withSpecial = true)
    {
        return $withSpecial ?
            $this->getRepository()->findAll() :
            $this->getRepository()->getAllExceptSlugs($this->getSpecialBoquettesSlugs());
    }

    private function getSpecialBoquettesSlugs()
    {
        return array('pians', 'cvis', 'brags', 'paniers');
    }

    private function getRepository()
    {
        return $this->em->getRepository('PJMAppBundle:Boquette');
    }

    public function canManage(User $user, Boquette $boquette)
    {
        return $this->em->getRepository('PJMAppBundle:Responsable')->estNiveauUn($user, $boquette);
    }

}