<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    public function indexAction()
    {
        return $this->render('PJMAppBundle:App:index.html.twig');
    }

    public function aProposAction()
    {
        return $this->render('PJMAppBundle:App:a_propos.html.twig');
    }
}
