<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PJM\UserBundle\Entity\User;
use PJM\AppBundle\Entity\Media\Photo;
use PJM\UserBundle\Form\Type\UserType;
use PJM\AppBundle\Form\Type\Media\PhotoType;

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

    public function encartAction(Request $request, User $user, $content = false)
    {
        if ($content) { // si on clique sur le lien d'encart
            $online = $this->getDoctrine()->getManager()
                ->getRepository('PJMUserBundle:User')
                ->getOneActive($user);

                return $this->render('PJMAppBundle:Profil:encart_content.html.twig', array(
                    'user' => $user,
                    'online' => isset($online)
                ));
        }

        return $this->render('PJMAppBundle:Profil:encart.html.twig', array(
            'user' => $user
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

    public function modifierPhotoAction(Request $request, $nouvelle)
    {
        $user = $this->getUser();

        $photo = $user->getPhoto();
        if ($nouvelle) {
            $photo = new Photo();
        } else {
            if ($photo === null) {
                return $this->redirect($this->generateUrl('pjm_profil_changerPhoto'));
            }
        }

        $form = $this->createForm(new PhotoType(), $photo, array(
            'ajout' => $nouvelle
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($nouvelle) {
                $photo->setProprietaire($user);
                $user->setPhoto($photo);
                $em->persist($user);
            } else {
                $em->persist($photo);
            }

            $em->flush();

            return $this->redirect($this->generateUrl('fos_user_profile_show'));
        }

        return $this->render('PJMAppBundle:Profil:modifierPhoto.html.twig', array(
            'form' => $form->createView(),
            'nouvelle' => $nouvelle,
            'photo' => $photo
        ));
    }

    /*
     * Annuaire
     */
    public function annuaireAction()
    {
        $datatable = $this->get("pjm.datatable.annuaire");
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Profil:annuaire.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Action ajax de rendu de l'annuaire.
     */
    public function annuaireResultsAction()
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.annuaire"));
        return $datatable->getResponse();
    }
}
