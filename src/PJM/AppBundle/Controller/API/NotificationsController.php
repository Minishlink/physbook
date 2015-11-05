<?php

namespace PJM\AppBundle\Controller\API;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/notifications")
 */
class NotificationsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/last")
     * @Method("GET")
     */
    public function lastAction(Request $request)
    {
        $endpoint = $request->query->get('endpoint');

        if (empty($endpoint)) {
            throw $this->createAccessDeniedException('Endpoint missing.');
        }

        $notification = $this->get('pjm.services.notification')->getLastNotificationByPushEndpoint($endpoint);

        if (!$notification) {
            throw $this->createAccessDeniedException('Cannot retrieve notification.');
        }

        return new JsonResponse(array(
            'notification' => $notification,
        ));
    }
}
