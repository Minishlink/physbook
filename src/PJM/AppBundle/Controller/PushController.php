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
        $message = new AndroidMessage();
        $message->setGCM(true);

        $message->setMessage('Test notification');
        $message->setDeviceIdentifier('subscriptionId');

        $this->container->get('rms_push_notifications')->send($message);

        return new Response('OK');
    }
}
