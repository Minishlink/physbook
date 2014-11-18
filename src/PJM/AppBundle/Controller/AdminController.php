<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use PJM\AppBundle\Form\Admin\NewUserType;

class AdminController extends Controller
{
    public function indexAction()
    {
        return $this->render('PJMAppBundle:Admin:index.html.twig');
    }

    // TODO gérer promos à l'ec'ss ou pas
    public function listeAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        return $this->render('PJMAppBundle:Admin:users_liste.html.twig', array(
            'users' => $users
        ));
    }

    public function inscriptionListeAction()
    {
        // updateUser($user, false); *X
        // $this->getDoctrine()->getManager()->flush();

        return $this->render('PJMAppBundle:Admin:users_new_users.html.twig', array(
            //'users' => $users
        ));
    }

    public function inscriptionUniqueAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $form = $this->createForm(new NewUserType(), $user, array(
            'action' => $this->generateUrl('pjm_app_admin_users_inscriptionUnique'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO faire ça dans un listener
            $user->setPassword(uniqid());
            $user->setUsername($user->getFams().$user->getTabagns().$user->getProms());

            // TODO envoyer password par mail

            $userManager->updateUser($user);

            $request->getSession()->getFlashBag()->add(
                'success',
                'Utilisateur ajouté.'
            );

            return $this->redirect($this->generateUrl('pjm_app_admin_users_inscriptionUnique'));
        }

        return $this->render('PJMAppBundle:Admin:users_new_user.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
