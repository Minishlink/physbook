<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PiansController extends Controller
{
    public function __construct()
    {
        $this->slug = 'pians';
    }

    public function indexAction(Request $request)
    {
        $utils = $this->get('pjm.services.utils');
        $piansService = $this->get('pjm.services.boquette.pians');
        $boquette = $piansService->getBoquette();
        $listeHistoriques = $utils->getHistorique($this->getUser(), $this->slug, 5);
        $boissonDuMois = $utils->getFeaturedItem($this->slug);

        return $this->render('PJMAppBundle:Consos:Pians/index.html.twig', array(
            'boquette' => $boquette,
            'boquetteSlug' => $this->slug,
            'solde' => $piansService->getSolde($this->getUser()),
            'listeHistoriques' => $listeHistoriques,
            'boissonDuMois' => $boissonDuMois,
            'listeHpi' => $this->get('pjm.services.compte_manager')->getComptesWithLessThan(-3000, $boquette),
        ));
    }
}
