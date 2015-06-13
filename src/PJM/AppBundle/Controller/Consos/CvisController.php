<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Component\HttpFoundation\Request;

class CvisController extends BoquetteController
{
    public function __construct()
    {
        $this->slug = 'cvis';
    }

    public function indexAction(Request $request)
    {
        $utils = $this->get('pjm.services.utils');
        $listeHistoriques = $utils->getHistorique($this->getUser(), $this->slug, 5);
        $produitMoment = $utils->getFeaturedItem($this->slug);
        $ziConsommateurs = $this->getTopConsommateurs(date('m')); // top du mois en cours
        $listeProduits = $this->getItems(true, 5);

        $stats = array(
            'achats' => $this->compterAchatsBoquette(date('m')),
            'saucisson' => $this->compterAchatsItem('Saucisson', date('m')),
            'burgers' => $this->compterAchatsItem('Cheese Burger', date('m'))
        );

        return $this->render('PJMAppBundle:Consos:Cvis/index.html.twig', array(
            'boquetteSlug' => $this->slug,
            'solde' => $this->getSolde(),
            'listeHistoriques' => $listeHistoriques,
            'ziConsommateurs' => $ziConsommateurs,
            'produitMoment' => $produitMoment,
            'listeProduits' => $listeProduits,
            'stats' => $stats
        ));
    }

    /*
    * ADMIN
    */
    public function adminAction()
    {
        // TODO faire reloguer l'utilisateur sauf si redirection depuis l'admin

        return $this->render('PJMAppBundle:Admin:Consos/Cvis/index.html.twig', array(
            'boquetteSlug' => $this->slug
        ));
    }
}
