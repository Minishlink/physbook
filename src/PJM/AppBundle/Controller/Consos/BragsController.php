<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class BragsController extends Controller
{
    public function indexAction(Request $request)
    {
        $resHandleFormRechargement = $this->handleFormRechargement($request);
        $formRechargement = $resHandleFormRechargement['form'];
        $message = $resHandleFormRechargement['message'];

        return $this->render('PJMAppBundle:Consos:brags.html.twig', array(
            'formRechargement' => $formRechargement->createView(),
            'message' => isset($message) ? $message : null
        ));
    }

    private function handleFormRechargement(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('montant', 'money', array(
                'constraints' => array(
                new NotBlank(),
                ),
            ))
        ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $message = array(
                'niveau' => 'success',
                'contenu' => 'Rechargement de '.$data['montant'].'€ effectué.'
            );
        }

        return array(
            'form' => $form,
            'message' => isset($message) ? $message : null
        );
    }
}
