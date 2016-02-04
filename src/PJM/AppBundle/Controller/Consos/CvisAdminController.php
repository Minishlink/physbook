<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CvisAdminController extends Controller
{
    public function indexAction()
    {
        return $this->render('PJMAppBundle:Admin:Consos/Cvis/index.html.twig', array(
            'boquette' => $this->get('pjm.services.boquette.cvis')->getBoquette(),
        ));
    }
}
