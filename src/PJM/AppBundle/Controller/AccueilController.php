<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AccueilController extends Controller
{
    public function indexAction()
    {
        $utils = $this->get('pjm.services.boquette');
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

        $listeEvents = $em->getRepository('PJMAppBundle:Event\Evenement')
            ->getEvents($this->getUser(), 3);

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
