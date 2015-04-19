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
    public function manageSubscriptionAction(Request $request, $action = false)
    {
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
                $em->remove($pushSubscription);
                $em->flush();
            }
        } else {
            // on vérifie que le subscription est déjà enregistrée
            if($pushSubscription !== null) {
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
