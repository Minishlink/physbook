<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use PJM\AppBundle\Entity\Consos\Transfert;
use PJM\AppBundle\Form\Type\Consos\TransfertType;

class BanqueController extends Controller
{
    public function indexAction()
    {
        $datatable_transactions = $this->get('pjm.datatable.credits');
        $datatable_transactions->setAdmin(false);
        $datatable_transactions->setAjaxUrl($this->generateUrl('pjm_app_banque_transactionsResults'));
        $datatable_transactions->buildDatatable();

        $datatable_achats = $this->get('pjm.datatable.achats');
        $datatable_achats->setAdmin(false);
        $datatable_achats->setAjaxUrl($this->generateUrl('pjm_app_banque_achatsResults'));
        $datatable_achats->buildDatatable();

        $datatable_transferts = $this->get('pjm.datatable.transferts');
        $datatable_transferts->setAdmin(false);
        $datatable_transferts->setAjaxUrl($this->generateUrl('pjm_app_banque_transfertsResults'));
        $datatable_transferts->buildDatatable();

        return $this->render('PJMAppBundle:Consos:Banque/index.html.twig', array(
            'datatable_transactions' => $datatable_transactions,
            'datatable_achats' => $datatable_achats,
            'datatable_transferts' => $datatable_transferts,
        ));
    }

    public function transfertAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $transfert = new Transfert();

        $form = $this->createForm(new TransfertType(), $transfert, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_banque_transfert'),
            'user' => $this->getUser(),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($transfert->getEmetteur()->getSolde() < $transfert->getMontant()) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        "Tu ne peux pas faire le transfert car tu serais en négat's après l'opération."
                    );
                } else {
                    $repo_compte = $em->getRepository('PJMAppBundle:Compte');
                    $receveur = $repo_compte->findOneBy(array(
                        'boquette' => $transfert->getEmetteur()->getBoquette(),
                        'user' => $transfert->getReceveurUser(),
                    ));
                    $transfert->setReceveur($receveur);

                    $this->get('pjm.services.transfert_manager')->traiter($transfert);

                    if ($transfert->getStatus() == 'OK') {
                        $success = true;

                        $request->getSession()->getFlashBag()->add(
                            'success',
                            'Tu as bien transféré '.($transfert->getMontant() / 100).'€ de ton compte '.$transfert->getEmetteur()->getBoquette().' à '.$transfert->getReceveur()->getUser().'.'
                        );
                    } else {
                        $request->getSession()->getFlashBag()->add(
                            'danger',
                            "Erreur : '".$transfert->getStatus()."'. Contacte un ZiPhy'sbook en lui communiquant cette erreur."
                        );
                    }
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors du transfert. Réessaye.'
                );

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
                    'success' => isset($success),
                ));

                return $response;
            }

            return $this->redirect($this->generateUrl('pjm_app_banque_index'));
        }

        return $this->render('PJMAppBundle:Consos:Banque/transfert.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Action ajax de rendu de la liste des transactions de l'user.
     */
    public function transactionsResultsAction()
    {
        $datatable = $this->get('pjm.datatable.credits');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Transaction');
        $query->addWhereAll($repository->callbackFindByUser($this->getUser()));

        return $query->getResponse();
    }

    /**
     * Action ajax de rendu de la liste des achats de l'user.
     */
    public function achatsResultsAction()
    {
        $datatable = $this->get('pjm.datatable.achats');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Historique');
        $query->addWhereAll($repository->callbackFindByUser($this->getUser()));

        return $query->getResponse();
    }

    /**
     * Action ajax de rendu de la liste des transferts de l'user.
     */
    public function transfertsResultsAction()
    {
        $datatable = $this->get('pjm.datatable.transferts');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Consos\Transfert');
        $query->addWhereAll($repository->callbackFindByUser($this->getUser()));

        return $query->getResponse();
    }
}
