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
        $historique = $utils->getHistoriqueComplet($this->getUser(), $this->slug, 5);

        return $this->render('PJMAppBundle:Consos:Pians/index.html.twig', array(
            'solde' => $this->getSolde(),
            'listeHistorique' => $historique,
        ));
    }
}
