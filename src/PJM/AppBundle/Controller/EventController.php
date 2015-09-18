<?php

namespace PJM\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
     *
     * @Template
     */
    public function indexAction(Request $request, Event\Evenement $event = null)
    {
        // si l'évènement choisi n'est pas visible on redirige vers l'accueil des évènements
        if (isset($event) && !$event->canBeSeenByUser($this->getUser())) {
            $request->getSession()->getFlashBag()->add(
                'warning',
                'Tu n\'as pas le droit d\'accéder à l\'évènement '.$event->getNom().'.'
            );

            return $this->redirect($this->generateUrl('pjm_app_event_index'));
        }

        $evenements = $this->get("pjm.services.evenement_manager")->get($event, $this->getUser(), 6);

        return array(
            'listeEvents' => $evenements['listeEvents'],
            'event' => $evenements['event']
        );
    }

    /**
     * Ajout d'un évènement.
     *
     * @param Request $request
     * @return object HTML Response
     * @throws \Exception
     *
     * @Template
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

                // on fait participer le créateur
                $this->get('pjm.services.invitation_manager')->toggleInscriptionFromUserToEvent(null, $this->getUser(), $event);

                $data = array(
                    'redirectURL' => $this->generateUrl('pjm_app_event_index', array('slug' => $event->getSlug()))
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Un problème est survenu lors de la création de l'évènement. Réessaye."
                );

                $data = array(
                    'formView' => $this->renderView('PJMAppBundle::form_only.html.twig', array(
                        'form' => $form->createView(),
                    )),
                    'flashBagView' => $this->renderView('PJMAppBundle:App:flashBag.html.twig'),
                    'success' => false,
                );
            }

            return $request->isXmlHttpRequest() ? new JsonResponse($data) : $this->redirect($this->generateUrl('pjm_app_event_index'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Ajout d'un évènement.
     *
     * @param Request $request
     * @param Event\Evenement $event
     * @return object HTML Response
     *
     * @Template
     */
    public function modifierAction(Request $request, Event\Evenement $event)
    {
        $eventManager = $this->get("pjm.services.evenement_manager");

        // on regarde si l'utilisateur est créateur
        if (!$eventManager->canEdit($this->getUser(), $event)) {
            $request->getSession()->getFlashBag()->add(
                'danger',
                'Tu n\'as pas les droits pour modifier cet évènement.'
            );

            return $this->redirect($this->generateUrl('pjm_app_event_index', array('slug' => $event->getSlug())));
        }

        $form = $this->createForm(new EvenementType(), $event, array(
            'action' => $this->generateUrl('pjm_app_event_modifier', array('slug' => $event->getSlug())),
            'user' => $this->getUser(),
            'label_submit' => 'Modifier',
        ));

        $isMajeurOriginal = $event->isMajeur();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $eventManager->update($event, $isMajeurOriginal);

                return $this->redirect($this->generateUrl('pjm_app_event_index', array('slug' => $event->getSlug())));
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Un problème est survenu lors de la modification de l'évènement. Réessaye."
                );
            }
        }

        return array(
            'form' => $form->createView(),
            'event' => $event,
        );
    }

    /**
     * Affiche et gère le bouton de suppression.
     *
     * @param Request $request
     * @param Event\Evenement $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Template
     */
    public function suppressionAction(Request $request, Event\Evenement $event)
    {
        $eventManager = $this->get("pjm.services.evenement_manager");

        // on regarde si l'utilisateur est créateur
        if (!$eventManager->canEdit($this->getUser(), $event)) {
            return array();
        }

        $form = $this->get('form.factory')->createNamedBuilder('form_suppression')
            ->setAction($this->generateUrl(
                'pjm_app_event_suppression',
                array('slug' => $event->getSlug())
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $eventManager->remove($event);
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Tes données ne sont pas valides.'
                );
            }

            return $this->redirect($this->generateUrl('pjm_app_event_index'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Affiche et gère le bouton d'inscription.
     *
     * @param Request $request
     * @param Event\Evenement $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Template
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

        return array(
            'form' => $form->createView(),
            'estPresent' => ($invitation !== null && $invitation->getEstPresent()),
        );
    }

    /**
     * Affiche l'état du paiement (côté utilisateur).
     *
     * @param Event\Evenement $event
     * @return Response
     *
     * @Template
     */
    public function etatPaiementUserAction(Event\Evenement $event)
    {
        // on va chercher l'invitation de l'utilisateur
        $invitation = $this->get('pjm.services.invitation_manager')->getInvitationFromUserToEvent($this->getUser(), $event);
        $inscrit = isset($invitation) && $invitation->getEstPresent();

        if ($event->isPaid()) {
            return array(
                'inscrit' => $inscrit,
                'event' => $event
            );
        }

        // on va chercher le compte de l'utilisateur
        $compte = $this->get('pjm.services.boquette.pians')->getCompte($this->getUser());

        // on regarde si l'utilisateur a assez d'argent
        $montantRechargement = $event->getPrix() - $compte->getSolde();

        return array(
            'inscrit' => $inscrit,
            'event' => $event,
            'montantRechargement' => $montantRechargement
        );
    }

    /**
     * Affiche et gère le formulaire d'invitations.
     *
     * @param Request $request
     * @param Event\Evenement $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Template
     */
    public function inviteAction(Request $request, Event\Evenement $event)
    {
        $form = $this->createForm(new UserPickerType(), null, array(
            'label_users' => false,
            'notIncludeUsers' => $event->getInvites(null, true),
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

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Affiche et gère le formulaire de déclenchement des paiements.
     *
     * @param Request $request
     * @param Event\Evenement $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Template
     */
    public function paiementAction(Request $request, Event\Evenement $event)
    {
        $eventManager = $this->get('pjm.services.evenement_manager');

        if (!$eventManager->canTriggerPayment($this->getUser(), $event)) {
            return array();
        }

        $form = $this->get('form.factory')->createNamedBuilder('form_paiement')
            ->setAction($this->generateUrl(
                'pjm_app_event_paiement',
                array('slug' => $event->getSlug())
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $eventManager->paiement($event);
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Tes données ne sont pas valides.'
                );
            }

            return $this->redirect($this->generateUrl('pjm_app_event_index', array('slug' => $event->getSlug())));
        }

        return array(
            'form' => $form->createView(),
        );
    }
}
