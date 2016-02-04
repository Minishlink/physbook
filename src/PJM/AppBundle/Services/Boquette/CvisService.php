<?php

namespace PJM\AppBundle\Services\Boquette;

use Doctrine\ORM\EntityManager;

class CvisService extends BoquetteService
{
    public function __construct(EntityManager $em, $specialBoquettes)
    {
        parent::__construct($em, $specialBoquettes['epicerie']);
    }
}
