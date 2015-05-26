<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use PJM\AppBundle\Form\Event\EvenementType;
use PJM\AppBundle\Entity\Event;

class EventController extends Controller
{
    /**
     * Accueil des évènements
     * @return object HTML Response
     */
    public function indexAction(Event\Evenement $event = null)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('PJMAppBundle:Event\Evenement');
        $nombreMax = 6;

        if ($event == null) {
            // on va chercher les $nombreMax-1 premiers events à partir de ce moment
            $listeEvents = $repo->getEvents($this->getUser(), $nombreMax-1);
            if (!empty($listeEvents)) {
                $event = $listeEvents[0];
            }
        } else {
            // on va chercher les $nombreMax-2 évènements après cet event
            $listeEvents = $repo->getEvents($this->getUser(), $nombreMax-2, 'after', $event->getDateDebut());

            $listeEvents = array_merge(array($event), $listeEvents);
        }

        if ($event !== null) {
            // on va chercher les events manquants avant
            $eventsARajouter = $repo->getEvents($this->getUser(), $nombreMax - count($listeEvents), 'before', $event->getDateDebut());
            $listeEvents = array_merge($eventsARajouter, $listeEvents);

            // on regarde si l'utilisateur est invité
            $invitation = $em->getRepository('PJMAppBundle:Event\Invitation')
                ->findOneBy(array("invite" => $this->getUser(), "event" => $event));
        } else {
            $invitation = null;
        }

        return $this->render('PJMAppBundle:Event:index.html.twig', array(
            'listeEvents' => $listeEvents,
            'event' => $event,
            'invitation' => $invitation
        ));
    }

    /**
     * Ajout d'un évènement
     * @return object HTML Response
     */
    public function nouveauAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $event = new Event\Evenement();
        $event->setCreateur($this->getUser());

        $form = $this->createForm(new EvenementType(), $event, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_event_nouveau'),
            'user' => $this->getUser()
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $em->persist($event);
                $em->flush();

                $success = true;

                $request->getSession()->getFlashBag()->add(
                    'success',
                    "L'évènement a été créé."
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Un problème est survenu lors de la création de l'évènement. Réessaye."
                );
            }

            if ($request->isXmlHttpRequest()) {
                $formView = $this->renderView('PJMAppBundle::form_only.html.twig', array(
                    'form' => $form->createView(),
                ));

                $flashBagView = $this->renderView('PJMAppBundle:App:flashBag.html.twig');

                $response = new JsonResponse();
                $response->setData(array(
                    'formView' => $formView,
                    'flashBagView' => $flashBagView,
                    'success' => isset($success)
                ));

                return $response;
            }

            return $this->redirect($this->generateUrl('pjm_app_event_index'));
        }

        return $this->render('PJMAppBundle:Event:nouveau.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
