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
     * @param Request $request
     * @param bool    $action
     * @param string  $endpoint
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/manage/{action}/{endpoint}", options={"expose"=true})
     * @Method("POST")
     */
    public function manageAction(Request $request, $action, $endpoint)
    {
        $annuler = ($action == 'annuler') ? true : false;

        // on va chercher la pushSubscription avec le même subscriptionId et endpoint
        $em = $this->getDoctrine()->getManager();
        $pushSubscription = $em->getRepository('PJMAppBundle:PushSubscription')
            ->findOneBy(array(
                'endpoint' => $endpoint,
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
                    ->setEndpoint($endpoint)
                    ->setUser($this->getUser())
                    ->setBrowserUA($request->server->get('HTTP_USER_AGENT', 'Unknown'))
                ;

                $em->persist($pushSubscription);
                $em->flush();
            }
        }

        return new JsonResponse(array(
            'success' => true
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
     * @return Response
     *
     * @Route("/delete", options={"expose"=true})
     * @Method("POST")
     */
    public function deleteAction(Request $request)
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

        return new Response('PushSubscription(s) removed.');
    }
}
