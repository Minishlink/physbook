<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use PJM\AppBundle\Entity\Inbox\Message;
use PJM\AppBundle\Entity\Inbox\Reception;
use PJM\UserBundle\Entity\User;
use PJM\AppBundle\Form\Inbox\MessageType;
use PJM\AppBundle\Form\Filter\UserFilterType;

class InboxController extends Controller
{
    /**
     * Accueil de la messagerie
     * @return object HTML Response
     */
    public function indexAction()
    {
        $inbox = $this->getUser()->getInbox();

        return $this->render('PJMAppBundle:Inbox:index.html.twig', array(
            'inbox' => $inbox
        ));
    }

    /**
     * Action de nouveau message
     * @return object HTML Response
     * @ParamConverter("user", options={"mapping": {"user": "username"}})
     */
    public function nouveauAction(Request $request, User $user = null, $annonce = false)
    {
        $message = new Message();
        $form = $this->createForm(new MessageType(), $message, array(
            'method' => 'POST',
            'user' => $this->getUser(),
            'annonce' => $annonce
        ));

        if ($user !== null) {
            $destinations = new \Doctrine\Common\Collections\ArrayCollection();
            $destinations->add($user->getInbox());
            $form->get('destinations')->setData($destinations);
        }

        $filterForm = $this->get('form.factory')->create(new UserFilterType());
        $filterForm->handleRequest($request);

        $form->handleRequest($request);

        if ($filterForm->isSubmitted()) {
            if ($filterForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $user_repo = $em->getRepository('PJMUserBundle:User');

                $filterBuilder = $user_repo->createQueryBuilder('u');
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $filterBuilder);

                $users = $filterBuilder->getQuery()->getResult();

                if ($users !== null) {
                    $destinations = new \Doctrine\Common\Collections\ArrayCollection();
                    foreach ($users as $user) {
                        $destinations->add($user->getInbox());
                    }
                    $form->get('destinations')->setData($destinations);
                    $user = null;
                }
            }
        }

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $message->setExpedition($this->getUser()->getInbox());
                $message->setAnnonce($annonce);

                if($annonce) {
                    if ($message->getBoquette() === null) {
                        throw $this->createAccessDeniedException('Il faut être responsable de boquette.');
                    }
                }

                $destinataires = array();
                foreach($message->getReceptions() as $reception) {
                    $destinataires[] = $reception->getInbox()->getUser()->getUsername();
                }
                $message->setDestinataires($destinataires);

                $em->persist($message);
                $em->flush();

                // on envoit les notifications push
                $type = $annonce ? null : 'message';
                $msg = $annonce ? "Nouvelle annonce !" : "Nouveau message !";
                $push = $this->get('pjm.services.push');
                $push->sendNotificationToUsers($message->getDestinatairesUsers(), $msg, $type);

                return $this->redirect($this->generateUrl('pjm_app_inbox_index'));
            }

            $request->getSession()->getFlashBag()->add(
                'danger',
                'Un problème est survenu lors de l\'envoi. Réessaye.'
            );
        }

        return $this->render('PJMAppBundle:Inbox:nouveau.html.twig', array(
            'form' => $form->createView(),
            'formFilter' => $filterForm->createView(),
            'destinataire' => isset($user) ? $user : null,
            'annonce' => $annonce,
        ));
    }

    /**
     * Marque comme lu un message reçu
     * @param  object Reception $reception Message reçu à marquer comme lu
     * @return object   HTTP Response
     */
    public function luAction(Request $request, Reception $reception)
    {
        if ($request->isXmlHttpRequest()) {
            if ($reception->getInbox()->getUser() == $this->getUser()) {
                if (!$reception->getLu()) {
                    $em = $this->getDoctrine()->getManager();
                    $reception->setLu(true);
                    $em->persist($reception);
                    $em->flush();
                }

                return new Response('Ok');
            }
        }

        return new Response('Pas Ajax', 400);
    }


    /**
     * Supprime un message reçu
     * @param  object Reception $reception Message reçu à supprimer
     * @return object   HTTP Response
     */
    public function supprimerAction(Request $request, Reception $reception)
    {
        if ($request->isXmlHttpRequest()) {
            if ($reception->getInbox()->getUser() == $this->getUser()) {
                $em = $this->getDoctrine()->getManager();
                $reception->setLu(true);
                $em->remove($reception);
                $em->flush();

                return new Response('Ok');
            }
        }

        return new Response('Pas Ajax', 400);
    }

    /**
     * Annule l'envoi d'un message, supprime pour tous les destinataires
     * @param  object Message $message Message à supprimer
     * @return object   HTTP Response
     */
    public function annulerAction(Request $request, Message $message)
    {
        if ($request->isXmlHttpRequest()) {
            if ($message->getExpediteur() == $this->getUser()) {
                $em = $this->getDoctrine()->getManager();
                foreach ($message->getReceptions() as $reception) {
                    $reception->setLu(true);
                }
                $em->remove($message);
                $em->flush();

                return new Response('Ok');
            }
        }

        return new Response('Pas Ajax', 400);
    }
}
