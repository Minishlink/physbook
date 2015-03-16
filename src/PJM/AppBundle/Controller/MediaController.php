<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use PJM\AppBundle\Entity\Media\Photo;
use PJM\AppBundle\Form\Media\PhotoType;
use PJM\UserBundle\Entity\User;

class MediaController extends Controller
{
    /**
     * [ADMIN] Gère les photos
     *
     * @param object Request $request Requête
     */
    public function gestionPhotosAction(Request $request, Photo $photo = null)
    {
        $ajout = ($photo === null);
        if($ajout) {
            $photo = new Photo();
            $urlAction = $this->generateUrl('pjm_app_admin_media_gestionPhotos');
        } else {
            $urlAction = $this->generateUrl('pjm_app_admin_media_gestionPhotos', array(
                'photo' => $photo->getId()
            ));
        }

        $form = $this->createForm(new PhotoType(), $photo, array(
            'method' => 'POST',
            'action' => $urlAction,
            'ajout' => $ajout
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($photo);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'La photo a bien été ajoutée ou modifiée.'
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de l\'ajout ou de la modification de la photo. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }
        }

        $datatable = $this->get("pjm.datatable.admin.media.photos");
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:Media/gestionPhotos.html.twig', array(
            'ajout' => $ajout,
            'form' => $form->createView(),
            'datatable' => $datatable
        ));
    }

    /**
     * [ADMIN] Va chercher toutes les entités Photo
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function photosResultsAction()
    {
        $datatable = $this->get("pjm.datatable.admin.media.photos");
        $datatable->setTwigExt($this->get('pjm.twig.intranet_extension'));
        $datatableData = $this->get("sg_datatables.datatable")->getDatatable($datatable);

        return $datatableData->getResponse();
    }
}
