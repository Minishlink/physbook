<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ConsosController extends Controller
{
    public function historiqueAction(Request $request)
    {
        $historiqueDatatable = $this->get("pjm.datatable.historique");
        $historiqueDatatable->buildDatatableView();

        return $this->render('PJMAppBundle:Consos:historique.html.twig', array(
            "datatable" => $historiqueDatatable,
        ));
    }

    public function historiqueResultsAction()
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.historique"));
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Historique');
        $datatable->addWhereBuilderCallback($repository->callbackFindByUser($this->getUser()));

        return $datatable->getResponse();
    }
}
