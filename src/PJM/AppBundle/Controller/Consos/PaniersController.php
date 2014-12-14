<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaniersController extends BoquetteController
{
    public function __construct()
    {
        $this->slug = 'paniers';
    }

    public function indexAction(Request $request)
    {
        return $this->render('PJMAppBundle:Consos:Paniers/index.html.twig', array(
            'solde' => $this->getSolde(),
        ));
    }
}
