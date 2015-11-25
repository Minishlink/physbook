<?php

namespace PJM\AppBundle\Controller\API;

use PJM\AppBundle\Entity\PushSubscription;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/pushSubscriptions")
 * @Security("has_role('ROLE_USER')")
 */
class PushSubscriptionController extends Controller
{
    /**
     * @param string $endpoint
     * @return JsonResponse
     *
     * @Route("/create/{endpoint}", options={"expose"=true})
     * @Method("POST")
     */
    public function createAction($endpoint)
    {
        $this->get('pjm.services.pushsubscriptions_manager')->create($this->getUser(), $endpoint);

        return new JsonResponse(array(
            'success' => true
        ));
    }

    /**
     * @param PushSubscription|null $pushSubscription
     * @param string $endpoint
     * @return JsonResponse
     *
     * @Route("/update/{endpoint}", options={"expose"=true})
     * @Method("POST")
     */
    public function updateAction(PushSubscription $pushSubscription = null, $endpoint)
    {
        $pushSubscriptionManager = $this->get('pjm.services.pushsubscriptions_manager');

        if (!$pushSubscription) {
            $pushSubscription = $pushSubscriptionManager->create($this->getUser(), $endpoint);
        } else {
            $pushSubscription = $pushSubscriptionManager->update($this->getUser(), $pushSubscription);
        }

        return new JsonResponse(array(
            'success' => isset($pushSubscription)
        ));
    }

    /**
     * @param PushSubscription  $pushSubscription
     * @return JsonResponse
     *
     * @Route("/delete/{endpoint}", options={"expose"=true})
     * @Method("POST")
     */
    public function deleteAction(PushSubscription $pushSubscription)
    {
        $success = $this->get('pjm.services.pushsubscriptions_manager')->delete($this->getUser(), $pushSubscription);

        return new JsonResponse(array(
            'success' => $success
        ));
    }

    /**
     * (DataTable) Results.
     *
     * @return Response
     *
     * @Route("/results")
     * @Method("GET")
     */
    public function resultsAction()
    {
        $datatable = $this->get('pjm.datatable.pushsubscription');
        $datatable->buildDatatable();
        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $repository = $this->getDoctrine()->getManager()->getRepository('PJMAppBundle:PushSubscription');
        $query->addWhereAll($repository->callbackFindByUser($this->getUser()));
        return $query->getResponse();
    }

    /**
     * (DataTable) Delete choices.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/bulk/delete", options={"expose"=true})
     * @Method("POST")
     */
    public function bulkDeleteAction(Request $request)
    {
        $list = $request->request->get('data');

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:PushSubscription');

        foreach ($list as $choice) {
            $pushSubscription = $repository->find($choice['value']);

            if ($pushSubscription->getUser() != $this->getUser()) {
                throw $this->createAccessDeniedException();
            }

            $em->remove($pushSubscription);
        }

        $em->flush();

        return new JsonResponse(array('msg' => 'PushSubscription(s) removed.'));
    }
}
