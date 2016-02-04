<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PiansAdminController extends Controller
{
    public function indexAction()
    {
        return $this->render('PJMAppBundle:Admin:Consos/Pians/index.html.twig', array(
            'boquette' => $this->get('pjm.services.boquette.pians')->getBoquette(),
        ));
    }
}
