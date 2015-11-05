<?php

namespace PJM\AppBundle\Controller\Consos;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RechargementController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/confirm")
     */
    public function confirmAction(Request $request)
    {
        $this->get('pjm.services.payments.lydia')->confirmPayment($request);
        return new Response();
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/cancel")
     */
    public function cancelAction(Request $request)
    {
        $this->get('pjm.services.payments.lydia')->cancelPayment($request, 'LYDIA_ANNULATION');
        return new Response();
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/expire")
     */
    public function expireAction(Request $request)
    {
        $this->get('pjm.services.payments.lydia')->cancelPayment($request, 'LYDIA_EXPIRE');
        return new Response();
    }
}
