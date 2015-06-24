<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PJM\AppBundle\Entity\Media\Photo;
use PJM\AppBundle\Form\Type\Media\PhotoType;

class MediaController extends Controller
{
    /**
     * [ADMIN] Gère les photos.
     *
     * @param object Request $request Requête
     */
    public function gestionPhotosAction(Request $request, Photo $photo = null)
    {
        $ajout = ($photo === null);
        if ($ajout) {
            $photo = new Photo();
            $urlAction = $this->generateUrl('pjm_app_admin_media_gestionPhotos');
        } else {
            $urlAction = $this->generateUrl('pjm_app_admin_media_gestionPhotos', array(
                'photo' => $photo->getId(),
            ));
        }

        $form = $this->createForm(new PhotoType(), $photo, array(
            'method' => 'POST',
            'action' => $urlAction,
            'ajout' => $ajout,
            'proprietaire' => ($photo->getProprietaire() === null) ? 'admin' : null,
            'admin' => true,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                if ($photo->getPublication() == 3) {
                    // si on veut forcer l'affichage sur bonjour gadz'arts
                    $photoAChanger = $em->getRepository('PJMAppBundle:Media\Photo')
                        ->findOneByPublication(3);
                    if ($photoAChanger !== null && $photoAChanger != $photo) {
                        $photoAChanger->setPublication(2);
                        $em->persist($photoAChanger);
                    }
                }

                $em->persist($photo);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'La photo a bien été ajoutée ou modifiée.'
                );

                return $this->redirect($this->generateUrl('pjm_app_admin_media_gestionPhotos'));
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de l\'ajout ou de la modification de la photo. Réessaye.'
                );

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }
        }

        $datatable = $this->get('pjm.datatable.admin.media.photos');


        return $this->render('PJMAppBundle:Admin:Media/gestionPhotos.html.twig', array(
            'ajout' => $ajout,
            'form' => $form->createView(),
            'datatable' => $datatable,
        ));
    }

    /**
     * [ADMIN] Va chercher toutes les entités Photo.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function photosResultsAction()
    {
        $datatable = $this->get('pjm.datatable.admin.media.photos');
        $datatable->setTwigExt($this->get('pjm.twig.intranet_extension'));
        $datatable->setExtImage($this->get('pjm.services.image'));
        $datatableData = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $datatableData->getResponse();
    }

    /**
     * [ADMIN] Action ajax d'autorisation de publication de photos.
     */
    public function togglePublicationPhotosAction(Request $request, $autoriser)
    {
        if ($request->isXmlHttpRequest()) {
            $liste = $request->request->get('data');

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository("PJMAppBundle:Media\Photo");

            foreach ($liste as $choice) {
                $photo = $repository->find($choice['value']);
                if ($photo !== null) {
                    if ($autoriser) {
                        $photo->setPublication(2);
                    } else {
                        $photo->setPublication(1);
                    }
                    $em->persist($photo);
                }
            }

            $em->flush();

            return new Response('Photos toggled.');
        }

        return $this->redirect($this->generateUrl('pjm_app_homepage'));
    }

    /**
     * [ADMIN] Action ajax de suppression de photos.
     */
    public function supprimerPhotosAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $liste = $request->request->get('data');

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository("PJMAppBundle:Media\Photo");

            foreach ($liste as $choice) {
                $photo = $repository->find($choice['value']);
                if ($photo !== null) {
                    $user = $photo->getProprietaire();
                    if ($user !== null) {
                        if ($user->getPhoto() == $photo) {
                            $user->setPhoto(null);
                            $em->persist($user);
                        }
                    }

                    $em->remove($photo);
                }
            }

            $em->flush();

            return new Response('Photos removed.');
        }

        return $this->redirect($this->generateUrl('pjm_app_homepage'));
    }
}
