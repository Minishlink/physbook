<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PiansAdminController extends Controller
{
    private $slug;

    public function __construct()
    {
        $this->slug = 'pians';
    }

    public function indexAction()
    {
        return $this->render('PJMAppBundle:Admin:Consos/Pians/index.html.twig', array(
            'boquetteSlug' => $this->slug,
        ));
    }
}
