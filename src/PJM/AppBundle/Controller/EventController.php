<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class EventController extends Controller
{
    /**
     * Accueil des évènements
     * @return object HTML Response
     */
    public function indexAction()
    {
        return $this->render('PJMAppBundle:Event:index.html.twig');
    }
}
