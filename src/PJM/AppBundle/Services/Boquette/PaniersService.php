<?php

namespace PJM\AppBundle\Services\Boquette;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Entity\Item;

class PaniersService extends BoquetteService
{
    private $itemSlug = 'panier';

    public function __construct(EntityManager $em, $specialBoquettes)
    {
        parent::__construct($em, $specialBoquettes['paniers']);
    }

    public function getCurrentPanier()
    {
        return $this->em->getRepository('PJMAppBundle:Item')->getLastItem($this->itemSlug, 'any');
    }

    public function getCommande(Item $panier, User $user)
    {
        return $this->em->getRepository('PJMAppBundle:Historique')->findOneBy(array(
            'user' => $user,
            'item' => $panier,
            'valid' => true,
        ));
    }
}
