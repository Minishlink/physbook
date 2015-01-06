<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

use PJM\AppBundle\Entity\Item;

class PaniersController extends BoquetteController
{
    public function __construct()
    {
        $this->slug = 'paniers';
        $this->itemSlug = 'panier';
    }

    public function indexAction(Request $request)
    {
        return $this->render('PJMAppBundle:Consos:Paniers/index.html.twig', array(
            'solde' => $this->getSolde(),
            'prixPanier' => $this->getPrixPanier(),
        ));
    }

    public function getCurrentPanier()
    {
        $panier = $this->getItem($this->itemSlug);

        if (null === $panier) {
            $panier = new Item();
            $panier->setLibelle('Panier de fruits et légumes');
            $panier->setPrix(500);
            $panier->setSlug($this->itemSlug);
            $panier->setBoquette($this->getBoquette());
            $panier->setValid(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($panier);
            $em->flush();
        }

        return $panier;
    }

    public function getPrixPanier()
    {
        return $this->getCurrentPanier()->getPrix();
    }
}
