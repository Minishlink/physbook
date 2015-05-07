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
            $request->request->get('redirurl') : $this->generateUrl("pjm_app_homepage", array(), true);

        $zone = $request->request->get('zone') ?
            $request->request->get('zone') : 'residence';

        $connexion = $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED');

        $passType = $connexion ? 'text' : 'password';

        $form = $this->get('form.factory')->createNamedBuilder(null, 'form')
            ->setMethod('post')
            ->setAction($action)
            ->add('auth_user', 'text', array(
                'label' => "Nom d'utilisateur",
            ))
            ->add('auth_pass', $passType, array(
                'label' => "Mot de passe"
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
                'label' => 'Connexion',
                'attr' => array('value' => 'Connexion')
            ))
            ->getForm()
        ;

        // si on est connecté
        if ($connexion) {
            // TODO on vérifie que l'utilisateur a bien accès au R&z@l
            if (true) {
                $form->setData(array(
                    'auth_user' => $this->getUser()->getUsername(),
                    'auth_pass' => $this->getUser()->getPassword()
                ));
            }
        }

        return $this->render('PJMAppBundle:Boquette/Rezal/Internet:connexion.html.twig', array(
            'form' => $form->createView(),
            'autoConnect' => $connexion,
        ));
    }
}
