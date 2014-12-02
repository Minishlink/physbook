<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use PJM\UserBundle\Entity\User;
use PJM\UserBundle\Form\UserType;

class ProfilController extends Controller
{
    public function voirAction(Request $request, User $user = null)
    {
        if(!isset($user)) {
            $user = $this->getUser();
        }

        $online = $this->getDoctrine()->getManager()
            ->getRepository('PJMUserBundle:User')
            ->getOneActive($user);

        return $this->render('PJMAppBundle:Profil:voir.html.twig', array(
            'user' => $user,
            'online' => isset($online)
        ));
    }

    public function modifierAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(new UserType(), $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('fos_user_profile_show'));
        }

        return $this->render('PJMAppBundle:Profil:modifier.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
