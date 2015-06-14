<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CvisAdminController extends Controller
{
    private $slug;

    public function __construct()
    {
        $this->slug = 'cvis';
    }

    public function indexAction()
    {
        return $this->render('PJMAppBundle:Admin:Consos/Cvis/index.html.twig', array(
            'boquetteSlug' => $this->slug
        ));
    }
}
