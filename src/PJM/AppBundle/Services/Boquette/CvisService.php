<?php

namespace PJM\AppBundle\Services\Boquette;

use Doctrine\ORM\EntityManager;

class CvisService extends BoquetteService
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em);

        $this->slug = 'cvis';
    }
}
