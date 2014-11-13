<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PiansController extends Controller
{
    public function indexAction()
    {
        return $this->render('PJMAppBundle:Consos:pians.html.twig', array(
            'erreur' => isset($erreur) ? $erreur : null
        ));
    }
}
