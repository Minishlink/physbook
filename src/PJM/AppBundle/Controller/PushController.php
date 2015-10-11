<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use PJM\AppBundle\Entity\PushSubscription;

class PushController extends Controller
{
    /**
     * Action ajax de rendu de la liste des pushSubscriptions.
     */
    public function subscriptionResultsAction()
    {
        $datatable = $this->get('pjm.datatable.pushsubscription');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:PushSubscription');
        $query->addWhereAll($repository->callbackFindByUser($this->getUser()));

        return $query->getResponse();
    }

    /**
     * Action ajax de suppression de pushSubscription.
     */
    public function deleteSubscriptionAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $liste = $request->request->get('data');

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('PJMAppBundle:PushSubscription');

            foreach ($liste as $choice) {
                $pushSubscription = $repository->find($choice['value']);

                if ($pushSubscription->getUser() != $this->getUser()) {
                    return new Response("La PushSubscription ne correspond pas à l'utilisateur.", 403);
                }

                $em->remove($pushSubscription);
            }

            $em->flush();

            return new Response('PushSubscription(s) removed.');
        }

        return $this->redirect($this->generateUrl('pjm_app_homepage'));
    }

    /**
     * Action ajax de gestion d'une PushSubscription.
     */
    public function manageSubscriptionAction(Request $request, $action = false)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect($this->generateUrl('pjm_app_homepage'));
        }

        $annuler = ($action == 'annuler') ? true : false;
        $subscription = array(
            'id' => $request->request->get('id'),
            'endpoint' => $request->request->get('endpoint'),
        );

        if (empty($subscription['id']) && empty($subscription['endpoint'])) {
            return new JsonResponse(array(
                'success' => false,
                'done' => $action,
                'subscription' => $subscription,
            ));
        }

        // on va chercher la pushSubscription avec le même subscriptionId et endpoint
        $em = $this->getDoctrine()->getManager();
        $pushSubscription = $em->getRepository('PJMAppBundle:PushSubscription')
            ->findOneBy(array(
                'subscriptionId' => $subscription['id'],
                'endpoint' => $subscription['endpoint'],
            ))
        ;

        if ($annuler) {
            // on annule
            if ($pushSubscription !== null) {
                if ($pushSubscription->getUser() == $this->getUser()) {
                    $em->remove($pushSubscription);
                    $em->flush();
                }
            }
        } else {
            // on vérifie que le subscription est déjà enregistrée
            if ($pushSubscription !== null) {
                // si oui, on met à jour le lastSubscribed
                if ($pushSubscription->getUser() == $this->getUser()) {
                    $pushSubscription->refreshLastSubscribed();
                    $em->persist($pushSubscription);
                    $em->flush();
                }
            } else {
                // si non, on l'ajoute
                $pushSubscription = new PushSubscription();
                $pushSubscription
                    ->setSubscriptionId($subscription['id'])
                    ->setEndpoint($subscription['endpoint'])
                    ->setUser($this->getUser())
                    ->setBrowserUA($request->server->get('HTTP_USER_AGENT', 'Unknown'))
                ;

                $em->persist($pushSubscription);
                $em->flush();
            }
        }

        return new JsonResponse(array(
            'success' => true,
            'done' => $action,
            'subscription' => $subscription,
        ));
    }
}
