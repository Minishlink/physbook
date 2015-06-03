<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use PJM\AppBundle\Form\Event\EvenementType;
use PJM\AppBundle\Form\Type\UserPickerType;
use PJM\AppBundle\Entity\Event;

class EventController extends Controller
{
    /**
     * Accueil des évènements
     * @return object HTML Response
     */
    public function indexAction(Request $request, Event\Evenement $event = null)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('PJMAppBundle:Event\Evenement');
        $nombreMax = 6;

        $droitVue = ($event === null) || $event->canBeSeenByUser($this->getUser());
        if (!$droitVue) {
            $request->getSession()->getFlashBag()->add(
                'warning',
                "Tu n'as pas le droit d'accéder à l'évènement ".$event->getNom()."."
            );
        }

        if ($event === null || !$droitVue) {
            // on va chercher les $nombreMax-1 premiers events à partir de ce moment
            $listeEvents = $repo->getEvents($this->getUser(), $nombreMax-1);
            if (!empty($listeEvents)) {
                $event = $listeEvents[0];

                if (!$droitVue) {
                    return $this->redirect($this->generateUrl('pjm_app_event_index', array('slug' => $event->getSlug())));
                }
            }
        } else {
            // on va chercher les $nombreMax-2 évènements après cet event
            $listeEvents = $repo->getEvents($this->getUser(), $nombreMax-2, 'after', $event->getDateDebut());

            $listeEvents = array_merge(array($event), $listeEvents);
        }

        if ($event !== null) {
            // on va chercher les events manquants avant
            $eventsARajouter = $repo->getEvents($this->getUser(), $nombreMax - count($listeEvents), 'before', $event->getDateDebut());

            // on regarde si l'utilisateur est invité
            $invitation = $em->getRepository('PJMAppBundle:Event\Invitation')
                ->findOneBy(array("invite" => $this->getUser(), "event" => $event));
        } else {
            // on va chercher les events manquants avant aujourd'hui
            $eventsARajouter = $repo->getEvents($this->getUser(), $nombreMax - count($listeEvents), 'before', new \DateTime());

            if (!empty($eventsARajouter)) {
                $event = $eventsARajouter[0];
            }

            $invitation = null;
        }

        $listeEvents = array_merge($eventsARajouter, $listeEvents);

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

    /**
     * Affiche et gère le bouton d'inscription
     * @param  object   Evenenement $event L'évènement considéré
     */
    public function inscriptionAction(Request $request, Event\Evenement $event)
    {
        $em = $this->getDoctrine()->getManager();

        // on regarde si l'utilisateur est invité
        $invitation = $em->getRepository('PJMAppBundle:Event\Invitation')
            ->findOneBy(array("invite" => $this->getUser(), "event" => $event));

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl(
                "pjm_app_event_inscription",
                array('slug' => $event->getSlug())
            ))
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($invitation !== null) {
                    // si on est déjà un invité
                    $invitation->setEstPresent(empty($invitation->getEstPresent()));
                    $em->persist($invitation);
                    $em->flush();

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        "Ta modification a bien été prise en compte."
                    );
                } else {
                    // sinon on vérifie que l'on peut accéder à cet évènement
                    if ($event->getIsPublic()) {
                        //on crée une nouvelle invitation
                        $invitation = new Event\Invitation();
                        $invitation->setEvent($event);
                        $invitation->setInvite($this->getUser());
                        $invitation->setEstPresent(true);
                        $em->persist($invitation);
                        $em->flush();

                        $request->getSession()->getFlashBag()->add(
                            'success',
                            "Tu participes bien à cet évènement."
                        );
                    } else {
                        $request->getSession()->getFlashBag()->add(
                            'warning',
                            "Tu n'as pas accès à cet évènement."
                        );
                    }
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Tes données ne sont pas valides."
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
     * Affiche et gère le formulaire d'invitations
     * @param  object   Evenenement $event L'évènement considéré
     */
    public function inviteAction(Request $request, Event\Evenement $event)
    {
        $em = $this->getDoctrine()->getManager();

        $invitationsNotInclude = $em->getRepository('PJMAppBundle:Event\Invitation')
            ->findByEvent($event);

        $notIncludeUsers = $event->getInvites(null, true);

        $form = $this->createForm(new UserPickerType(), null, array(
            'label_users' => false,
            'notIncludeUsers' => $notIncludeUsers,
            'method' => 'POST',
            'action' => $this->generateUrl(
                "pjm_app_event_invite",
                array('slug' => $event->getSlug())
            ),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();

            if ($form->isValid()) {
                $users = $data['users'];
                $usersFilter = array();

                if ($form->get('filtre')->isClicked()) {
                    // on traite le filtre
                    $em = $this->getDoctrine()->getManager();
                    $user_repo = $em->getRepository('PJMUserBundle:User');

                    $filterBuilder = $user_repo->createQueryBuilder('u');
                    $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $filterBuilder);

                    $usersFilter = $filterBuilder->getQuery()->getResult();
                }

                $users = array_unique(array_merge($users->toArray(), $usersFilter));

                foreach ($users as $user) {
                    // on vérifie que c'est un utilisateur
                    if ('PJM\UserBundle\Entity\User' == get_class($user)) {
                        // on vérifie qu'il n'est pas déjà invité
                        $invitation = $em->getRepository('PJMAppBundle:Event\Invitation')
                            ->findOneBy(array("invite" => $user, "event" => $event));

                        if ($invitation === null) {
                            $invitation = new Event\Invitation();
                            $invitation->setEvent($event);
                            $invitation->setInvite($user);
                            $em->persist($invitation);

                            //TODO notification
                        }
                    }
                }

                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    "Tes invitations ont bien été envoyées."
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
