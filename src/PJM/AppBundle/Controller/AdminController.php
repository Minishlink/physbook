<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
    }

    public function inscriptionUniqueAction()
    {
        // updateUser($user);
    }
}
