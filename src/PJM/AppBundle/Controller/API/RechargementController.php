<?php

namespace PJM\AppBundle\Controller\API;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/rechargement")
 */
class RechargementController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/confirm")
     * @Method("POST")
     */
    public function confirmAction(Request $request)
    {
        $this->get('pjm.services.payments.lydia')->handlePayment($request, 'OK');
        return new Response();
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/cancel")
     * @Method("POST")
     */
    public function cancelAction(Request $request)
    {
        $this->get('pjm.services.payments.lydia')->handlePayment($request, 'LYDIA_ANNULATION');
        return new Response();
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/expire")
     * @Method("POST")
     */
    public function expireAction(Request $request)
    {
        $this->get('pjm.services.payments.lydia')->handlePayment($request, 'LYDIA_EXPIRE');
        return new Response();
    }
}
