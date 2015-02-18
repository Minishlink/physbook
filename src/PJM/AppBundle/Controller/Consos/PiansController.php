<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PiansController extends BoquetteController
{
    public function __construct()
    {
        $this->slug = 'pians';
    }

    public function indexAction(Request $request)
    {
        $utils = $this->get('pjm.services.utils');
        $historique = $utils->getHistorique($this->getUser(), $this->slug, 5);
        $boissonDuMois = $utils->getFeaturedItem($this->slug);

        return $this->render('PJMAppBundle:Consos:Pians/index.html.twig', array(
            'boquetteSlug' => $this->slug,
            'solde' => $this->getSolde(),
            'listeHistorique' => $historique,
            'boissonDuMois' => $boissonDuMois,
        ));
    }

    /*
    * ADMIN
    */
    public function adminAction()
    {
        // TODO faire reloguer l'utilisateur sauf si redirection depuis l'admin

        return $this->render('PJMAppBundle:Admin:Consos/Pians/index.html.twig', array(
            'boquetteSlug' => $this->slug
        ));
    }
}
