<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use PJM\AppBundle\Entity\Inbox\Message;
use PJM\AppBundle\Form\Inbox\MessageType;

class InboxController extends Controller
{
    public function indexAction()
    {
        $inbox = $this->getUser()->getInbox();

        return $this->render('PJMAppBundle:Inbox:index.html.twig', array(
            'inbox' => $inbox
        ));
    }

    public function nouveauAction(Request $request)
    {
        $message = new Message();
        $form = $this->createForm(new MessageType(), $message, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_inbox_nouveau'),
        ));

        $form->handleRequest($request);

         if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $message->setExpedition($this->getUser()->getInbox());
                $em->persist($message);
                $em->flush();

                return $this->redirect($this->generateUrl('pjm_app_inbox_index'));
            }

            $request->getSession()->getFlashBag()->add(
                'danger',
                'Un problème est survenu lors de l\'envoi. Réessaye.'
            );
         }

        return $this->render('PJMAppBundle:Inbox:nouveau.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function luAction(Request $request, Message $message)
    {

    }
}
