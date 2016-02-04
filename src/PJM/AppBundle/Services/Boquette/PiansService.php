<?php

namespace PJM\AppBundle\Services\Boquette;

use Doctrine\ORM\EntityManager;

class PiansService extends BoquetteService
{
    public function __construct(EntityManager $em, $specialBoquettes)
    {
        parent::__construct($em, $specialBoquettes['bar']);
    }
}
