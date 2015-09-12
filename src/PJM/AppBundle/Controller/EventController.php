<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use PJM\AppBundle\Form\Type\Event\EvenementType;
use PJM\AppBundle\Form\Type\UserPickerType;
use PJM\AppBundle\Entity\Event;

class EventController extends Controller
{
    /**
     * Accueil des évènements.
     *
     * @param Request $request
     * @param Event\Evenement $event
     * @return object HTML Response
     */
    public function indexAction(Request $request, Event\Evenement $event = null)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('PJMAppBundle:Event\Evenement');
        $nombreMax = 6;

        // si l'évènement choisi n'est pas visible on redirige vers l'accueil des évènements
        if (isset($event) && !$event->canBeSeenByUser($this->getUser())) {
            $request->getSession()->getFlashBag()->add(
                'warning',
                "Tu n'as pas le droit d'accéder à l'évènement ".$event->getNom().'.'
            );

            return $this->redirect($this->generateUrl('pjm_app_event_index'));
        }

        // si c'est l'accueil des évènements
        if ($event === null) {
            // on va chercher les $nombreMax-1 premiers events à partir de ce moment
            $listeEvents = $repo->getEvents($this->getUser(), $nombreMax - 1);

            // on définit l'event en cours comme celui le plus proche de la date
            if (count($listeEvents) > 0) {
                $event = $listeEvents[0];
            }
        } else {
            // on va chercher les $nombreMax-2 évènements après cet event
            $listeEvents = $repo->getEvents($this->getUser(), $nombreMax - 2, 'after', $event->getDateDebut());

            $listeEvents = array_merge(array($event), $listeEvents);
        }

        $dateRechercheAvant = ($event !== null) ? $event->getDateDebut() : new \DateTime();
        // on va chercher les events manquants avant
        $eventsARajouter = $repo->getEvents($this->getUser(), $nombreMax - count($listeEvents), 'before', $dateRechercheAvant, $event);

        $listeEvents = array_merge($eventsARajouter, $listeEvents);

        if ($event === null && count($listeEvents) > 0) {
            $event = end($listeEvents);
        }

        return $this->render('PJMAppBundle:Event:index.html.twig', array(
            'listeEvents' => $listeEvents,
            'event' => $event
        ));
    }

    /**
     * Ajout d'un évènement.
     *
     * @param Request $request
     * @return object HTML Response
     * @throws \Exception
     */
    public function nouveauAction(Request $request)
    {
        $eventManager = $this->get('pjm.services.evenement_manager');
        $event = $eventManager->create($this->getUser());

        $form = $this->createForm(new EvenementType(), $event, array(
            'action' => $this->generateUrl('pjm_app_event_nouveau'),
            'user' => $this->getUser(),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $eventManager->configure($event);
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
                    'success' => isset($success),
                ));

                return $response;
            }

            return $this->redirect($this->generateUrl('pjm_app_event_index'));
        }

        return $this->render('PJMAppBundle:Event:nouveau.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Ajout d'un évènement.
     *
     * @param Request $request
     * @param Event\Evenement $event
     * @return object HTML Response
     */
    public function modifierAction(Request $request, Event\Evenement $event)
    {
        // on regarde si l'utilisateur est créateur
        if ($event->getCreateur() !== $this->getUser() && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return new Response("Tu n'as pas les droits pour modifier cet article.");
        }

        $form = $this->createForm(new EvenementType(), $event, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_event_modifier', array('slug' => $event->getSlug())),
            'user' => $this->getUser(),
            'label_submit' => 'Modifier',
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($event);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    "L'évènement a été modifié."
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Un problème est survenu lors de la création de l'évènement. Réessaye."
                );
            }

            return $this->redirect($this->generateUrl('pjm_app_event_index', array('slug' => $event->getSlug())));
        }

        return $this->render('PJMAppBundle:Event:modifier.html.twig', array(
            'form' => $form->createView(),
            'event' => $event,
        ));
    }

    /**
     * Affiche et gère le bouton de suppression.
     *
     * @param Request $request
     * @param Event\Evenement $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function suppressionAction(Request $request, Event\Evenement $event)
    {
        $em = $this->getDoctrine()->getManager();

        // on regarde si l'utilisateur est créateur
        if ($event->getCreateur() !== $this->getUser() && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return new Response("Tu n'as pas les droits pour supprimer cet article.");
        }

        $form = $this->get('form.factory')->createNamedBuilder('form_suppression')
            ->setAction($this->generateUrl(
                'pjm_app_event_suppression',
                array('slug' => $event->getSlug())
            ))
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->remove($event);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    "L'évènement a été supprimé."
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Tes données ne sont pas valides.'
                );
            }

            return $this->redirect($this->generateUrl('pjm_app_event_index'));
        }

        return $this->render('PJMAppBundle:Event:form_suppression.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Affiche et gère le bouton d'inscription.
     *
     * @param Request $request
     * @param Event\Evenement $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function inscriptionAction(Request $request, Event\Evenement $event)
    {
        $invitationManager = $this->get('pjm.services.invitation_manager');

        // on regarde si l'utilisateur est invité
        $invitation = $invitationManager->getInvitationFromUserToEvent($this->getUser(), $event);

        $form = $this->get('form.factory')->createNamedBuilder('form_inscription')
            ->setAction($this->generateUrl(
                'pjm_app_event_inscription',
                array('slug' => $event->getSlug())
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $invitationManager->toggleInscriptionFromUserToEvent($invitation, $this->getUser(), $event);
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Tes données ne sont pas valides.'
                );
            }

            return $this->redirect($this->generateUrl('pjm_app_event_index', array('slug' => $event->getSlug())));
        }

        return $this->render('PJMAppBundle:Event:form_inscription.html.twig', array(
            'form' => $form->createView(),
            'estPresent' => ($invitation !== null && $invitation->getEstPresent()),
        ));
    }

    /**
     * Affiche et gère le formulaire d'invitations.
     *
     * @param Request $request
     * @param Event\Evenement $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function inviteAction(Request $request, Event\Evenement $event)
    {
        $form = $this->createForm(new UserPickerType(), null, array(
            'label_users' => false,
            'notIncludeUsers' => $event->getInvites(null, true),
            'method' => 'POST',
            'action' => $this->generateUrl(
                'pjm_app_event_invite',
                array('slug' => $event->getSlug())
            ),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($form->get('filtre')->isClicked()) {
                    // on traite le filtre
                    $filterBuilder = $this->getDoctrine()->getManager()->getRepository('PJMAppBundle:User')->createQueryBuilder('u');
                    $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $filterBuilder);
                    $usersFilter = $filterBuilder->getQuery()->getResult();
                }

                $this->get('pjm.services.invitation_manager')->sendInvitations(
                    array_unique(array_merge(
                        $form->getData()['users']->toArray(),
                        isset($usersFilter) ? $usersFilter : array()
                    )),
                    $event
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu. Réessaye.'
                );

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_event_index', array('slug' => $event->getSlug())));
        }

        return $this->render('PJMAppBundle:Event:form_invite.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
