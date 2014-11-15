<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;

class BragsController extends Controller
{
    public function indexAction(Request $request, $messages = null)
    {
        $formRechargement = $this->createFormBuilder()
            ->add('montant', 'money', array(
                'constraints' => array(
                new NotBlank(),
                new Range(array(
                    'min' => 1,
                    'minMessage' => 'Le montant doit être supérieur à 1€.',
                )),
            )))
        ->getForm();

        $formRechargement->handleRequest($request);
        $data = $formRechargement->getData();
        $montant = $data['montant'];

        if ($formRechargement->isValid()) {
            // on redirige vers S-Money
            $resRechargement = json_decode(
                $this->forward('PJMAppBundle:Consos/Rechargement:getURL', array(
                    'montant' => $montant*100,
                    'caisseSMoney' => 'aeensambordeaux',
                    'boquette' => 'brags'
                ))->getContent(),
                true
            );

            if ($resRechargement['valid'] === true) {
                // succès, on redirige vers l'URL de paiement
                // TODO pour app iphone
                return $this->redirect($resRechargement['url']);
            } else {
                // erreur
                $messages[] = array(
                    'niveau' => 'danger',
                    'contenu' => 'Il y a eu une erreur lors de la communication avec S-Money.'
                );
                $messages[] = $resRechargement['message'];
            }
        } else {
            if (isset($montant) && $montant < 1) {
                $messages[] = array(
                    'niveau' => 'danger',
                    'contenu' => 'Le montant ('.$montant.'€) doit être supérieur à 1€.'
                );
            }
        }

        return $this->render('PJMAppBundle:Consos:brags.html.twig', array(
            'formRechargement' => $formRechargement->createView(),
            'messages' => isset($messages) ? $messages : null
        ));
    }
}
