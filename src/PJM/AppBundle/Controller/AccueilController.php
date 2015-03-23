<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use PJM\AppBundle\Entity\Inbox\Reception;

class AccueilController extends Controller
{
    public function indexAction()
    {
        $utils = $this->get('pjm.services.utils');
        $solde['brags'] = $utils->getSolde($this->getUser(), 'brags');
        $solde['pians'] = $utils->getSolde($this->getUser(), 'pians');
        $solde['paniers'] = $utils->getSolde($this->getUser(), 'paniers');
        $mazoutage = ($solde['pians'] < -300);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMUserBundle:User');
        $listeAnniv = $repository->getByDateAnniversaire(new \DateTime());
        $listeConnectes = $repository->getActive($this->getUser());

        $photo = $em->getRepository('PJMAppBundle:Media\Photo')
                        ->findOneByPublication(3);

        $annonces = $em->getRepository('PJMAppBundle:Inbox\Reception')
                        ->getAnnoncesByInbox($this->getUser()->getInbox(), false, 3);

        $listeEvents = array(
            array(
                'titre' => "Manip Quadriprom's",
                'organisateur' => "Archis",
                'date' => new \DateTime('2014-03-28'),
                'journee' => true,
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
            'listeConnectes' => $listeConnectes,
            'listeEvents' => $listeEvents,
            'annonces' => $annonces,
        ));
    }
}
