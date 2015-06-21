<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\JsonResponse;
use PJM\AppBundle\Entity\UsersHM;

class AppController extends Controller
{
    public function contactAction(Request $request)
    {
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
                ->setSubject('[Physbook] [Contact] '.$data['sujet'])
                ->setFrom(array($user->getEmail() => $user->getUsername()))
                ->setTo(array('contact@physbook.fr' => "ZiPhy'sbook"))
                ->setBody($data['contenu'])
            ;
            $this->get('mailer')->send($message);

            $request->getSession()->getFlashBag()->add(
                'success',
                'Message envoyé.'
            );

            return $this->redirect($this->generateUrl('pjm_app_contact'));
        }

        return $this->render('PJMAppBundle:App:contact.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function supportTechniqueAction()
    {
        return $this->render('PJMAppBundle:App:en_construction.html.twig');
    }

    /**
     * Affiche et gère un bouton Phy's HM.
     *
     * @param object   UsersHM $usersHM Le lien usersHM entre les users et l'article, item etc...
     */
    public function physHMAction(Request $request, UsersHM $usersHM)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder($usersHM)
            ->setAction($this->generateUrl(
                'pjm_app_physHM',
                array('usersHM' => $usersHM->getId())
            ))
            ->setMethod('POST')
            ->add('save', 'submit', array(
                'label' => "Phy's HM",
                'attr' => array(
                    'class' => 'physHM',
                    'title' => "Phy's HM",
                ),
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $json = array();

            if ($form->isValid()) {
                if (!$usersHM->getUsers()->contains($this->getUser())) {
                    $usersHM->addUser($this->getUser());
                    $em->persist($usersHM);
                    $em->flush();

                    $json = array(
                        'success' => true,
                    );
                } else {
                    $json = array(
                        'success' => false,
                        'reason' => 'Déjà HM',
                    );
                }
            } else {
                // erreur dans le formulaire
                foreach ($form->getErrors() as $error) {
                    $reason[] = $error->getMessage();
                }

                $json = array(
                    'success' => false,
                    'reason' => $reason,
                );
            }

            $response = new JsonResponse();
            $response->setData($json);

            return $response;
        }

        return $this->render('PJMAppBundle::form_standard.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
