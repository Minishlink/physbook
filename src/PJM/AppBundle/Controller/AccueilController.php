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
        return $this->render('PJMAppBundle:Accueil:etatComptes.html.twig', array(
            'solde' => null
        ));
    }

    public function anniversairesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMUserBundle:User');

        $listeAnniv = $repository->findByAnniversaire(new \DateTime());

        dump($listeAnniv);

        return $this->render('PJMAppBundle:Accueil:anniversaires.html.twig', array(
            'listeAnniv' => $listeAnniv,
        ));
    }
}
