<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;

use PJM\AppBundle\Entity\Consos\Transfert;
use PJM\AppBundle\Form\Consos\TransfertType;

class BanqueController extends Controller
{
    public function indexAction(Request $request)
    {
        return $this->render('PJMAppBundle:Consos:Banque/index.html.twig', array(

        ));
    }

    public function transfertAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $transfert = new Transfert();

        $form = $this->createForm(new TransfertType(), $transfert, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_banque_transfert'),
            'user' => $this->getUser()
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $repo_compte = $em->getRepository('PJMAppBundle:Compte');
                $receveur = $repo_compte->findOneBy(array(
                    'boquette' => $transfert->getEmetteur()->getBoquette(),
                    'user' => $transfert->getReceveurUser()
                ));
                $transfert->setReceveur($receveur);
                $transfert->finaliser();

                if ($transfert->getEmetteur()->getSolde() < 0) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        "Tu ne peux pas faire le transfert car tu serais en négat's après l'opération."
                    );
                } else {
                    // si pians on crédite sur le serveur Pi

                    $em->persist($transfert);
                    $em->flush();

                    $success = true;

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        'Tu as bien transféré '.($transfert->getMontant()/100).'€ de ton compte '.$transfert->getEmetteur()->getBoquette().' à '.$transfert->getReceveur()->getUser().'.'
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors du transfert. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            if ($request->isXmlHttpRequest()) {
                $formView = $this->renderView('PJMAppBundle::form_only.html.twig', array(
                    'form' => $form->createView(),
                ));

                $flashBagView = $this->renderView('PJMAppBundle:App:flashBag.html.twig');

                $response = new JsonResponse();
                $response->setData(array(
                    'formView' => $formView,
                    'flashBagView' => $flashBagView,
                    'success' => isset($success)
                ));

                return $response;
            }

            return $this->redirect($this->generateUrl('pjm_app_banque_index'));
        }

        //$datatable = $this->get("pjm.datatable.consos.transfert");
        //$datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Consos:Banque/transfert.html.twig', array(
            'form' => $form->createView(),
            //'datatable' => $datatable
        ));
    }
}
