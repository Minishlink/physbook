<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AccueilController extends Controller
{
    public function indexAction()
    {
        return $this->render('PJMAppBundle:Accueil:index.html.twig');
    }

    public function etatComptesAction()
    {
        $utils = $this->get('pjm.services.utils');
        $solde['brags'] = $utils->getSolde($this->getUser(), 'brags');
        $solde['pians'] = $utils->getSolde($this->getUser(), 'pians');
        $solde['paniers'] = $utils->getSolde($this->getUser(), 'paniers');

        return $this->render('PJMAppBundle:Accueil:etatComptes.html.twig', array(
            'solde' => $solde
        ));
    }

    public function anniversairesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMUserBundle:User');

        $listeAnniv = $repository->findByAnniversaire(new \DateTime());

        return $this->render('PJMAppBundle:Accueil:anniversaires.html.twig', array(
            'listeAnniv' => $listeAnniv,
        ));
    }

    public function bonjourGadzartsAction()
    {
        $photo = array(
            'url' => 'images/accueil/Niatur.jpg',
            'legende' => 'Niatur aime la bonne wave',
            'hm' => 123
        );

        return $this->render('PJMAppBundle:Accueil:bonjourGadzarts.html.twig', array(
            'photo' => $photo,
        ));
    }
}
