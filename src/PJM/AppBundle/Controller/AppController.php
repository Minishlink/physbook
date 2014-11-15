<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AppController extends Controller
{
    public function indexAction()
    {
        return $this->render('PJMAppBundle:App:index.html.twig');
    }

    public function contactAction(Request $request)
    {
        $envoi = false;
        $user = $this->getUser();

        $form = $this->createFormBuilder()
            ->add('sujet', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3)),
                ),
            ))
            ->add('contenu', 'textarea', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 20)),
                ),
            ))
        ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $message = \Swift_Message::newInstance()
                ->setSubject("[Physbook] [Contact] ".$data['sujet'])
                ->setFrom(array($user->getEmail() => $user->getUsername()))
                ->setTo(array('louis.lagrange@gadz.org' => 'Louis Lagrange'))
                ->setBody($data['contenu'])
            ;
            $this->get('mailer')->send($message);

            $envoi = true;
        }

        return $this->render('PJMAppBundle:App:contact.html.twig', array(
            'form' => $form->createView(),
            'envoi' => $envoi
        ));
    }

    public function supportTechniqueAction()
    {
        return $this->render('PJMAppBundle:App:en_construction.html.twig');
        //return $this->render('PJMAppBundle:App:support_technique.html.twig');
    }
}
