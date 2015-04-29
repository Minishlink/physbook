<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use RMS\PushNotificationsBundle\Message\AndroidMessage;

use PJM\AppBundle\Entity\PushSubscription;

class PushController extends Controller
{
    /**
     * Action d'affichage de la page des réglages des notifications
     */
    public function reglagesAction(Request $request)
    {
        $datatable_push = $this->get("pjm.datatable.pushsubscription");
        $datatable_push->buildDatatableView();

        return $this->render('PJMAppBundle:Notifications:reglages.html.twig', array(
            'datatable_push' => $datatable_push,
        ));
    }

    /**
     * Action ajax de rendu de la liste des pushSubscriptions.
     */
    public function subscriptionResultsAction()
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.pushsubscription"));
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:PushSubscription');
        $datatable->addWhereBuilderCallback($repository->callbackFindByUser($this->getUser()));

        return $datatable->getResponse();
    }

    /**
     * Action ajax de suppression de pushSubscription.
     */
    public function deleteSubscriptionAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $liste = $request->request->get("data");

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository("PJMAppBundle:PushSubscription");

            foreach ($liste as $choice) {
                $pushSubscription = $repository->find($choice["value"]);

                if ($pushSubscription->getUser() != $this->getUser()) {
                    return new Response("La PushSubscription ne correspond pas à l'utilisateur.", 403);
                }

                $em->remove($pushSubscription);
            }

            $em->flush();

            return new Response("PushSubscription(s) removed.");
        }

        return new Response("This is not ajax.", 400);
    }

    /**
     * Action ajax de gestion d'une PushSubscription.
     */
    public function manageSubscriptionAction(Request $request, $action = false)
    {
        if (!$request->isXmlHttpRequest()) {
            return new Response("This is not ajax.", 400);
        }

        $annuler = ($action == 'annuler') ? true : false;
        $subscription = array(
            'id' => $request->request->get('id'),
            'endpoint' => $request->request->get('endpoint')
        );

        if (empty($subscription['id']) && empty($subscription['id'])) {
            $json = array(
                'success' => false,
                'done' => $action,
                'subscription' => $subscription
            );

            $response = new JsonResponse();
            $response->setData($json);
            return $response;
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
            if($pushSubscription !== null) {
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
                    ->setBrowserUA($_SERVER['HTTP_USER_AGENT'])
                ;

                $em->persist($pushSubscription);
                $em->flush();
            }
        }

        $json = array(
            'success' => true,
            'done' => $action,
            'subscription' => $subscription
        );

        $response = new JsonResponse();
        $response->setData($json);
        return $response;
    }

    public function sendNotificationAction(Request $request)
    {
        $push = $this->get('pjm.services.push');
        $push->sendNotificationToUser($this->getUser(), 'test');

        return new Response('OK');
    }
}
