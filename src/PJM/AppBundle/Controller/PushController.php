<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use RMS\PushNotificationsBundle\Message\AndroidMessage;

class PushController extends Controller
{
    public function manageSubscriptionAction(Request $request, $annuler = false)
    {
        $subscription = array(
            'id' => $request->request->get('id'),
            'endpoint' => $request->request->get('endpoint')
        );

        $subscriptions = $this->getUser()->getPushSubscriptions();

        if ($annuler) {
            // on va chercher la pushsubscription avec le même subscriptionId
            // on annule
        } else {
            // on va chercher les subscirptions de l'utilisateur
            // on vérifie que le subscription est déjà enregistré
            // si oui, on met à jour le lastSubscribed
            // si non, on l'ajoute
        }

        $json = array(
            'success' => true,
            'done' => $annuler,
            'subscription' => $subscription
        );

        $response = new JsonResponse();
        $response->setData($json);
        return $response;
    }

    public function sendNotificationAction(Request $request)
    {
        $push = $this->get('pjm.services.push');
        //$push->sendNotificationToUser($this->getUser(), 'test');
        $push->sendNotificationToSubscriptionId('', 'test');

        return new Response('OK');
    }
}
