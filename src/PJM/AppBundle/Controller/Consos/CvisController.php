<?php

namespace PJM\AppBundle\Controller\Consos;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CvisController extends Controller
{
    public function indexAction()
    {
        $utils = $this->get('pjm.services.utils');
        $cvisService = $this->get('pjm.services.boquette.cvis');
        $boquette = $cvisService->getBoquette();
        $listeHistoriques = $utils->getHistorique($this->getUser(), $boquette->getSlug(), 5);
        $produitMoment = $utils->getFeaturedItem($boquette->getSlug());
        $ziConsommateurs = $cvisService->getTopConsommateurs(date('m')); // top du mois en cours
        $listeProduits = $cvisService->getItems(true, 5);

        $stats = array(
            'achats' => $cvisService->compterAchatsBoquette(date('m')),
            'saucisson' => $cvisService->compterAchatsItem('Saucisson', date('m')),
            'burgers' => $cvisService->compterAchatsItem('Cheese Burger', date('m')),
        );

        return $this->render('PJMAppBundle:Consos:Cvis/index.html.twig', array(
            'boquette' => $boquette,
            'solde' => $cvisService->getSolde($this->getUser()),
            'listeHistoriques' => $listeHistoriques,
            'ziConsommateurs' => $ziConsommateurs,
            'produitMoment' => $produitMoment,
            'listeProduits' => $listeProduits,
            'stats' => $stats,
        ));
    }

    /**
     * @Template("PJMAppBundle:Boquette:nav.html.twig")
     *
     * @return array
     */
    public function navAction()
    {
        return array(
            'boquette' => $this->get('pjm.services.boquette.cvis')->getBoquette(),
            'logo' => 'images/header/Cvis-B.png',
        );
    }
}
