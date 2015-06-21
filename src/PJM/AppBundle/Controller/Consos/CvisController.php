<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CvisController extends Controller
{
    private $slug;

    public function __construct()
    {
        $this->slug = 'cvis';
    }

    public function indexAction(Request $request)
    {
        $utils = $this->get('pjm.services.utils');
        $cvisService = $this->get('pjm.services.boquette.cvis');
        $listeHistoriques = $utils->getHistorique($this->getUser(), $this->slug, 5);
        $produitMoment = $utils->getFeaturedItem($this->slug);
        $ziConsommateurs = $cvisService->getTopConsommateurs(date('m')); // top du mois en cours
        $listeProduits = $cvisService->getItems(true, 5);

        $stats = array(
            'achats' => $cvisService->compterAchatsBoquette(date('m')),
            'saucisson' => $cvisService->compterAchatsItem('Saucisson', date('m')),
            'burgers' => $cvisService->compterAchatsItem('Cheese Burger', date('m')),
        );

        return $this->render('PJMAppBundle:Consos:Cvis/index.html.twig', array(
            'boquetteSlug' => $this->slug,
            'solde' => $cvisService->getSolde($this->getUser()),
            'listeHistoriques' => $listeHistoriques,
            'ziConsommateurs' => $ziConsommateurs,
            'produitMoment' => $produitMoment,
            'listeProduits' => $listeProduits,
            'stats' => $stats,
        ));
    }
}
