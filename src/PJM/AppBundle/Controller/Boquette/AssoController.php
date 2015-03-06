<?php

namespace PJM\AppBundle\Controller\Boquette;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AssoController extends Controller //extends BoquetteController
{
    public function __construct()
    {
        $this->slug = 'asso';
    }

    /*
    * ADMIN
    */
    public function adminAction()
    {
        // TODO faire reloguer l'utilisateur sauf si redirection depuis l'admin

        return $this->render('PJMAppBundle:Admin:Boquette/Asso/index.html.twig', array(
            'boquetteSlug' => $this->slug
        ));
    }
}
