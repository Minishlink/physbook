<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AccueilController extends Controller
{
    public function indexAction()
    {
        $utils = $this->get('pjm.services.utils');
        $solde['brags'] = $utils->getSolde($this->getUser(), 'brags');
        $solde['pians'] = $utils->getSolde($this->getUser(), 'pians');
        $solde['paniers'] = $utils->getSolde($this->getUser(), 'paniers');
        $mazoutage = ($solde['pians'] < 0);

        $photo = array(
            'url' => 'images/accueil/Niatur.jpg',
            'legende' => 'Niatur aime la bonne wave',
            'hm' => 123
        );

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMUserBundle:User');
        $listeAnniv = $repository->findByAnniversaire(new \DateTime());

        $listeEvents = array(
            array(
                'titre' => "Fin's de Nol's",
                'organisateur' => "Restal",
                'date' => new \DateTime('2014-12-19 19:00'),
                'couleur' => 'vert'
            ),
            array(
                'titre' => "Nuit des Fignos",
                'organisateur' => "CDF",
                'date' => new \DateTime('2015-01-24 22:00'),
                'couleur' => null
            ),
        );

        return $this->render('PJMAppBundle:Accueil:index.html.twig', array(
            'solde' => $solde,
            'mazoutage' => $mazoutage,
            'photo' => $photo,
            'listeAnniv' => $listeAnniv,
            'listeEvents' => $listeEvents,
        ));
    }
}
