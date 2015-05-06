<?php

namespace PJM\AppBundle\Controller\Boquette;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RezalController extends Controller
{
    /**
     * Action d'affichage de la page de connexion au R&z@l
     */
    public function connexionAction(Request $request)
    {
        $action = $request->request->get('action') ?
            $request->request->get('action') : 'http://172.17.0.1:8002/index.php';

        $redirurl = $request->request->get('redirurl') ?
            $request->request->get('redirurl') : $this->generateUrl("pjm_app_homepage");

        $zone = $request->request->get('zone') ?
            $request->request->get('zone') : 'residence';

        $form = $this->get('form.factory')->createNamedBuilder(null, 'form')
            ->setMethod('post')
            ->add('auth_user', 'text', array(
                'label' => "Nom d'utilisateur",
            ))
            ->add('auth_pass', 'password', array(
                'label' => "Mot de passe",
                'always_empty' => false
            ))
            ->add('redirurl', 'hidden', array(
                'data' => $redirurl
            ))
            ->add('zone', 'hidden', array(
                'data' => $zone
            ))
            ->add('action', 'hidden', array(
                'data' => $action
            ))
            ->add('accept', 'submit', array(
                'label' => 'Connexion'
            ))
            ->getForm()
        ;

        if ($request->request->get('auth_user')) {
            $form->handleRequest($request);
        }

        // si on a envoyé le formulaire
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $connexion = true;
            }
        } else {
            // si on est connecté, pas besoin d'afficher le formulaire
            if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                // TODO on vérifie que l'utilisateur a bien accès au R&z@l
                if (true) {
                    // on envoit le username et le mot de passe crypté au pfsense
                    $form->setData(array(
                        'login' => $this->getUser()->getUsername(),
                        'pass' => $this->getUser()->getPassword()
                    ));

                    $connexion = true;
                }
            }
        }

        return $this->render('PJMAppBundle:Boquette/Rezal/Internet:connexion.html.twig', array(
            'form' => $form->createView(),
            'connexion' => isset($connexion),
            'action' => isset($connexion) ? $action : ''
        ));
    }
}
