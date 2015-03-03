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
                'titre' => "Election Asso 214",
                'organisateur' => "CE",
                'date' => new \DateTime('2014-03-17 19:00'),
                'journee' => false,
                'couleur' => 'vert'
            ),
            array(
                'titre' => "Semaine SKZ",
                'organisateur' => "UE",
                'date' => new \DateTime('2015-04-18'),
                'journee' => true,
                'couleur' => null
            ),
            array(
                'titre' => "Grandes UAI",
                'organisateur' => "UAI",
                'date' => new \DateTime('2015-05-14'),
                'journee' => true,
                'couleur' => 'bleu-clair'
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
