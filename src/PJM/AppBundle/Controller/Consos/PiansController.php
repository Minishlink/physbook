<?php

namespace PJM\AppBundle\Controller\Consos;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PiansController extends Controller
{
    public function indexAction()
    {
        $utils = $this->get('pjm.services.utils');
        $piansService = $this->get('pjm.services.boquette.pians');
        $boquette = $piansService->getBoquette();
        $listeHistoriques = $utils->getHistorique($this->getUser(), $boquette->getSlug(), 5);
        $boissonDuMois = $utils->getFeaturedItem($boquette->getSlug());

        return $this->render('PJMAppBundle:Consos:Pians/index.html.twig', array(
            'boquette' => $boquette,
            'solde' => $piansService->getSolde($this->getUser()),
            'listeHistoriques' => $listeHistoriques,
            'boissonDuMois' => $boissonDuMois,
            'listeHpi' => $this->get('pjm.services.compte_manager')->getComptesWithLessThan(-3000, $boquette),
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
            'boquette' => $this->get('pjm.services.boquette.pians')->getBoquette(),
            'logo' => 'images/header/Pians-B.png',
        );
    }
}
