<?php

namespace PJM\AppBundle\Controller\Consos;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use PJM\AppBundle\Entity\Transaction;

class RechargementController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Route("/confirm")
     */
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

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/cancel")
     */
    public function cancelAction(Request $request)
    {
        $transactionId = $request->query->get('order_ref');
        $transaction = $this->getDoctrine()->getRepository('PJMAppBundle:Transaction')->getManager()->findOneById(substr($transactionId, 7));

        $transaction->setStatus('LYDIA_ANNULATION');

        return $this->redirect($this->generateUrl('pjm_app_lydia_rechargement_fail'));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/expire")
     */
    public function expireAction(Request $request)
    {
        $transactionId = $request->query->get('order_ref');
        $transaction = $this->getDoctrine()->getRepository('PJMAppBundle:Transaction')->getManager()->findOneById(substr($transactionId, 7));

        $transaction->setStatus('LYDIA_EXPIRE');

        return $this->redirect($this->generateUrl('pjm_app_lydia_rechargement_fail'));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/success")
     */
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

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/fail")
     */
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
