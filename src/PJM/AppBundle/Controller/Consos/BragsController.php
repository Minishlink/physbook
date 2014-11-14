<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;

class BragsController extends Controller
{
    public function indexAction(Request $request)
    {
        $resHandleFormRechargement = $this->handleFormRechargement($request);
        $formRechargement = $resHandleFormRechargement['form'];
        $messages = $resHandleFormRechargement['messages'];

        return $this->render('PJMAppBundle:Consos:brags.html.twig', array(
            'formRechargement' => $formRechargement->createView(),
            'messages' => isset($messages) ? $messages : null
        ));
    }

    private function handleFormRechargement(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('montant', 'money', array(
                'constraints' => array(
                new NotBlank(),
                new Range(array(
                    'min' => 1,
                    'minMessage' => 'Le montant doit être supérieur à 1€.',
                )),
            )))
        ->getForm();

        $form->handleRequest($request);
        $data = $form->getData();
        $montant = $data['montant'];

        if ($form->isValid()) {
            // on va chercher l'URL S-Money
            $rechargement = $this->container->get('pjm_app.rechargement');

            if($rechargement->rechargerSMoney()) {
                // succès
                $messages[] = array(
                    'niveau' => 'success',
                    'contenu' => 'Rechargement de '.$montant.'€ effectué.'
                );
            } else {
                // erreur
                $messages[] = array(
                    'niveau' => 'danger',
                    'contenu' => 'Il y a eu une erreur lors de la communication avec S-Money.'
                );
            }
        } else {
            if (isset($montant) && $montant < 1) {
                $messages[] = array(
                    'niveau' => 'danger',
                    'contenu' => 'Le montant ('.$montant.'€) doit être supérieur à 1€.'
                );
            }
        }

        return array(
            'form' => $form,
            'messages' => isset($messages) ? $messages : null
        );
    }
}
