<?php

namespace PJM\AppBundle\Services\Boquette;

use Doctrine\ORM\EntityManager;
use PJM\UserBundle\Entity\User;
use PJM\AppBundle\Entity\Item;

class PaniersService extends BoquetteService
{
    private $itemSlug;

    public function __construct(EntityManager $em)
    {
        parent::__construct($em);

        $this->slug = 'paniers';
        $this->itemSlug = 'panier';
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
