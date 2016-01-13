<?php

namespace PJM\AppBundle\Controller\Event;

use PJM\AppBundle\Form\Type\Filter\UserFilterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PJM\AppBundle\Form\Type\UserPickerType;
use PJM\AppBundle\Entity\Event;

class InvitationController extends Controller
{
    /**
     * Affiche et gère le bouton d'inscription.
     *
     * @param Request         $request
     * @param Event\Evenement $event
     *
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
                if ($invitationManager->toggleInscriptionFromUserToEvent(
                    $invitation,
                    $this->getUser(),
                    $event,
                    $this->get('pjm.services.boquette.pians')->getCompte($this->getUser())->getSolde()
                )) {
                    $this->get('pjm.services.evenement_manager')->checkPassageMajeur($event, 10);
                }
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
     * Affiche et gère le formulaire d'invitations.
     *
     * @param Request         $request
     * @param Event\Evenement $event
     *
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
                $this->get('pjm.services.invitation_manager')->sendInvitations($form->getData()['users']->toArray(), $event);
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
     * Affiche et gère le formulaire d'invitations par filtre.
     *
     * @param Request         $request
     * @param Event\Evenement $event
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Template
     */
    public function inviteBatchAction(Request $request, Event\Evenement $event)
    {
        $form = $this->createForm(new UserFilterType(), null, array(
            'submit' => 'Filtrer',
            'action' => $this->generateUrl(
                'pjm_app_event_inviteBatch',
                array('slug' => $event->getSlug())
            ),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on traite le filtre
                $usersFilter = $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions(
                    $form,
                    $this->getDoctrine()->getManager()->getRepository('PJMAppBundle:User')->createQueryBuilder('u')
                )->getQuery()->getResult();

                $this->get('pjm.services.invitation_manager')->sendInvitations($usersFilter, $event);
            }

            return $this->redirect($this->generateUrl('pjm_app_event_index', array('slug' => $event->getSlug())));
        }

        return array(
            'form' => $form->createView(),
        );
    }
}
