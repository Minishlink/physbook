<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use PJM\AppBundle\Entity\Transaction;

class RechargementController extends Controller
{
    public function getURLAction(Transaction $transaction)
    {
        $em = $this->getDoctrine()->getManager();
        // on met la transaction en queue pour récupérer l'id
        $em->persist($transaction);
        $em->flush();

        $buzz = $this->container->get('buzz');
        $curl = $buzz->getClient();
        $curl->setTimeout(30);

        $vendorToken = $this->container->getParameter('paiement.lydia.vendortoken');
        $providerToken = $this->container->getParameter('paiement.lydia.providertoken');
        $urlLydia = $this->container->getParameter('paiement.lydia.url');

        $content = array(
            'vendor_token' => $vendorToken,
            'provider_token' => $providerToken,
            'recipient' => '',
            'type' => 'email',
            'message' => "[Phy'sbook] ".$transaction->getCompte()->getBoquette()->getNom().' - '.$transaction->getCompte()->getUser()->getUsername(),
            'amount' => $transaction->getMontant(),
            'currency' => 'EUR',
            'expire_time' => 300,
            'confirm_url' => $this->generateUrl('pjm_app_boquette_rechargement_confirm'),
            'cancel_url' => $this->generateUrl('pjm_app_boquette_rechargement_cancel'),
            'expire_url' => $this->generateUrl('pjm_app_boquette_rechargement_expire'),
            'notify' => 'yes',
            'notify_collector' => 'no',
            'order_ref' => substr(uniqid(), 0, 6).'_'.$transaction->getId(),
        );

        $response = $buzz->post($urlLydia, $content);

        if ($response->getStatusCode() != 200) {
            // si échec

            $this->get('pjm.services.notification')->sendFlash(
                'warning',
                'Erreur '.$response->getStatusCode().': '.$response->getReasonPhrase()
            );

            return $this->redirect($this->generateUrl('pjm_app_boquette_'.$transaction->getCompte()->getBoquette()->getSlug().'_index'));

        } else {

            return $this->redirect($this->generateUrl('pjm_app_boquette_rechargement_confirm'));

        }
    }

    public function confirmAction(Request $request)
    {
        if (isset($transaction)) {
            if ($this->getUser() == $transaction->getCompte()->getUser()) {
                if (null !== $transaction->getStatus()) {
                    if ($transaction->getStatus() == 'OK') {
                        // si le paiement a été complété

                    } else {
                        // si le paiement a été annulé
                        $this->get('pjm.services.notification')->sendFlash(
                            'danger',
                            'Le rechargement de ' . $transaction->showMontant() . '€ n\'a pu être effectué.'
                        );

                        if (substr($transaction->getStatus(), 0, 5) == 'REZAL') {

                            $this->get('pjm.services.notification')->sendFlash(
                                'danger',
                                "Attention, l'erreur vient du serveur du R&z@l. Par conséquent, tu as été débité sur ton compte S-Money, mais pas crédité sur le serveur du R&z@l (relié aux bucqueurs au Pian's et au C'vis). Va voir l'harpag's pour te faire créditer ou rembourser."
                            );
                        }
                    }
                }

                return $this->redirect($this->generateUrl('pjm_app_boquette_'.$transaction->getCompte()->getBoquette()->getSlug().'_index'));
            } else {
                throw new HttpException(403, "Tu n'es pas l'auteur de cette transaction.");
            }
        }
        throw new HttpException(404, "La transaction n'existe pas.");

        if ($request->request->get('transactionId')) {
            $transactionId = $request->request->get('transactionId');
            $error = $request->request->get('error');

            $transaction = $this->getDoctrine()->getRepository('PJMAppBundle:Transaction')->getManager()->findOneById(substr($transactionId, 7));

            if (isset($transaction)) {
                if (null === $transaction->getStatus()) {
                    if ($error == 0) {
                        $transaction->setStatus('OK');
                    } else {
                        if ($error !== null) {
                            $transaction->setStatus($error);
                        } else {
                            $transaction->setStatus('NOK');
                        }
                    }

                    $this->get('pjm.services.transaction_manager')->traiter($transaction);

                    return new Response($transaction->getStatus() === 'OK' ? 'OK' : 'NOK');
                } else {
                    return new Response('Cette transaction a deja ete traitee.', 403);
                }
            }
            return new Response('Transaction inconnue', 404);
        }
        return $this->redirect($this->generateUrl('pjm_app_homepage'));
    }

    public function cancelAction(Request $request)
    {
        $transactionId = $request->query->get('order_ref');
        $transaction = $this->getDoctrine()->getRepository('PJMAppBundle:Transaction')->getManager()->findOneById(substr($transactionId, 7));

        $transaction->setStatus('LYDIA_ANNULATION');

        return $this->redirect($this->generateUrl('pjm_app_lydia_rechargement_fail'));
    }

    public function expireAction(Request $request)
    {
        $transactionId = $request->query->get('order_ref');
        $transaction = $this->getDoctrine()->getRepository('PJMAppBundle:Transaction')->getManager()->findOneById(substr($transactionId, 7));

        $transaction->setStatus('LYDIA_EXPIRE');

        return $this->redirect($this->generateUrl('pjm_app_lydia_rechargement_fail'));
    }

    public function successAction(Request $request)
    {
        $transactionId = $request->query->get('order_ref');
        $transaction = $this->getDoctrine()->getRepository('PJMAppBundle:Transaction')->getManager()->findOneById(substr($transactionId, 7));

        $this->get('pjm.services.notification')->sendFlash(
            'success',
            'Tu as bien rechargé ton compte de ' . $transaction->showMontant() . '€.'
        );

        return $this->redirect($this->generateUrl('pjm_app_boquette_'.$transaction->getCompte()->getBoquette()->getSlug().'_index'));
    }

    public function failAction(Request $request)
    {
        $transactionId = $request->query->get('order_ref');
        $transaction = $this->getDoctrine()->getRepository('PJMAppBundle:Transaction')->getManager()->findOneById(substr($transactionId, 7));

        $this->get('pjm.services.notification')->sendFlash(
            'warning',
            'Il n\'y a pas eu de suite à ta demande de rechargement de '.$transaction->showMontant().'€.'
        );

        return $this->redirect($this->generateUrl('pjm_app_boquette_'.$transaction->getCompte()->getBoquette()->getSlug().'_index'));
    }


}
