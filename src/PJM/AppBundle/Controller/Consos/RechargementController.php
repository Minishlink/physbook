<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            'browser_success_url' => '',
            'browser_fail_url' => '',
            'notify' => 'yes',
            'notify_collector' => 'no',
            'order_ref' => substr(uniqid(), 0, 6).'_'.$transaction->getId(),
        );

        $response = $buzz->post($urlLydia, $content);

        if ($response->getStatusCode() != 200) {
            // si échec
            $resData = array(
                'valid' => false,
            );

            $this->get('session')->getFlashBag()->add(
                'warning',
                'Erreur '.$response->getStatusCode().': '.$response->getReasonPhrase()
            );
        } else {
            // si on a une réponse valide de la part de S-Money
            $data = json_decode($response->getContent(), true);
            if (isset($data['url'])) {
                $resData = array(
                    'valid' => true,
                    'url' => $data['url'],
                );
            } else {
                $resData = array(
                    'valid' => false,
                );

                $this->get('session')->getFlashBag()->add(
                    'warning',
                    'Erreur '.$data['Code'].': '.$data['ErrorMessage']
                );
            }
        }

        $res = new JsonResponse();
        $res->setData($resData);

        return $res;
    }

    public function retourSMoneyAction(Request $request)
    {
        if ($request->request->get('transactionId')) {
            $transactionId = $request->request->get('transactionId');
            $status = $request->request->get('status');
            $errorCode = $request->request->get('errorCode');

            $transaction = $this->getDoctrine()->getRepository('PJMAppBundle:Transaction')->getManager()->findOneById(substr($transactionId, 7));

            if (isset($transaction)) {
                if (null === $transaction->getStatus()) {
                    if ($status == 'OK') {
                        $transaction->setStatus('OK');
                    } else {
                        if ($errorCode !== null) {
                            $transaction->setStatus($errorCode);
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

    public function redirectionDepuisSMoneyAction(Request $request)
    {
        $transactionId = $request->query->get('transactionId');

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PJMAppBundle:Transaction');
        $transaction = $repository->findOneById(substr($transactionId, 7));

        if (isset($transaction)) {
            if ($this->getUser() == $transaction->getCompte()->getUser()) {
                if (null !== $transaction->getStatus()) {
                    if ($transaction->getStatus() == 'OK') {
                        // si le paiement a été complété
                        $this->get('session')->getFlashBag()->add(
                            'success',
                            'Tu as bien rechargé ton compte de '.$transaction->showMontant().'€.'
                        );
                    } else {
                        // si le paiement a été annulé
                        $this->get('session')->getFlashBag()->add(
                            'danger',
                            'Le rechargement de '.$transaction->showMontant().'€ n\'a pu être effectué.'
                        );

                        switch ($transaction->getStatus()) {
                            case '623':
                                $source = "Phy'sbook";
                                break;
                            case '624':
                                $source = 'S-Money';
                                break;
                            case '625':
                                $source = 'Utilisateur';
                                break;
                            default:
                                $source = 'inconnue';
                                break;
                        }

                        if (substr($transaction->getStatus(), 0, 5) == 'REZAL') {
                            $source = 'Serveur R&z@l';

                            $this->get('session')->getFlashBag()->add(
                                'danger',
                                "Attention, l'erreur vient du serveur du R&z@l. Par conséquent, tu as été débité sur ton compte S-Money, mais pas crédité sur le serveur du R&z@l (relié aux bucqueurs au Pian's et au C'vis). Va voir l'harpag's pour te faire créditer ou rembourser."
                            );
                        }

                        $this->get('session')->getFlashBag()->add(
                            'warning',
                            'Code d\'erreur : '.$transaction->getStatus().' ('.$source.')'
                        );
                    }
                } else {
                    // si l'utilisateur n'est pas allé plus loin que la page sur S-Money
                    $this->get('session')->getFlashBag()->add(
                        'info',
                        'Il n\'y a pas eu de suite à ta demande de rechargement de '.$transaction->showMontant().'€.'
                    );
                }

                return $this->redirect($this->generateUrl('pjm_app_boquette_'.$transaction->getCompte()->getBoquette()->getSlug().'_index'));
            } else {
                throw new HttpException(403, "Tu n'es pas l'auteur de cette transaction.");
            }
        }

        throw new HttpException(404, "La transaction n'existe pas.");
    }
}
