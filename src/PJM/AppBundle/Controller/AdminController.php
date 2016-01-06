<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PJM\AppBundle\Form\Type\FileParserType;
use PJM\AppBundle\Form\Type\Admin\NewUserType;
use PJM\AppBundle\Form\Type\Admin\ResponsabiliteType;
use PJM\AppBundle\Form\Type\Admin\BoquetteType;
use PJM\AppBundle\Entity\Responsabilite;
use PJM\AppBundle\Entity\Boquette;

class AdminController extends Controller
{
    /**
     * Page d'accueil de la gestion du site.
     *
     * @return object Vue de la page
     */
    public function indexAction()
    {
        return $this->render('PJMAppBundle:Admin:index.html.twig');
    }

    /**
     * Gère les responsabilités.
     *
     * @param object Request $request Requête
     */
    public function responsabilitesAction(Request $request, Responsabilite $responsabilite = null)
    {
        $ajout = ($responsabilite === null);
        if ($ajout) {
            $responsabilite = new Responsabilite();
            $urlAction = $this->generateUrl('pjm_app_admin_responsabilites');
        } else {
            $urlAction = $this->generateUrl('pjm_app_admin_responsabilites', array(
                'responsabilite' => $responsabilite->getId(),
            ));
        }

        $form = $this->createForm(new ResponsabiliteType(), $responsabilite, array(
            'method' => 'POST',
            'action' => $urlAction,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($responsabilite);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'La responsabilité a bien été ajoutée ou modifiée.'
                );

                return $this->redirect($this->generateUrl('pjm_app_admin_responsabilites'));
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de l\'ajout ou de la modification de la responsabilité. Réessaye.'
                );

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }
        }

        $datatable = $this->get('pjm.datatable.admin.responsabilites');
        $datatable->buildDatatable();

        return $this->render('PJMAppBundle:Admin:responsabilites.html.twig', array(
            'ajout' => $ajout,
            'form' => $form->createView(),
            'datatable' => $datatable,
        ));
    }

    /**
     * Va chercher toutes les entités Responsabilite.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function responsabilitesResultsAction()
    {
        $datatable = $this->get('pjm.datatable.admin.responsabilites');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();
    }

    /**
     * Gère les boquettes.
     *
     * @param object Request $request Requête
     */
    public function gestionBoquettesAction(Request $request, Boquette $boquette = null)
    {
        $ajout = ($boquette === null);
        if ($ajout) {
            $boquette = new Boquette();
            $urlAction = $this->generateUrl('pjm_app_admin_gestionBoquettes');
        } else {
            $urlAction = $this->generateUrl('pjm_app_admin_gestionBoquettes', array(
                'boquette' => $boquette->getId(),
            ));
        }

        $form = $this->createForm(new BoquetteType(), $boquette, array(
            'action' => $urlAction,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($boquette);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'La boquette a bien été ajoutée ou modifiée.'
                );

                return $this->redirect($urlAction);
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de l\'ajout ou de la modification de la boquette. Réessaye.'
                );

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }
        }

        $datatable = $this->get('pjm.datatable.admin.boquettes');
        $datatable->buildDatatable();

        return $this->render('PJMAppBundle:Admin:gestionBoquettes.html.twig', array(
            'ajout' => $ajout,
            'form' => $form->createView(),
            'datatable' => $datatable,
        ));
    }

    /**
     * Va chercher toutes les entités Boquette.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function boquettesResultsAction()
    {
        $datatable = $this->get('pjm.datatable.admin.boquettes');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();
    }

    public function listeAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        return $this->render('PJMAppBundle:Admin:users_liste.html.twig', array(
            'users' => $users,
        ));
    }

    public function inscriptionListeAction(Request $request)
    {
        $form = $this->createForm(new FileParserType(), null, array(
            'action' => $this->generateUrl('pjm_app_admin_users_inscriptionListe'),
            'parserType' => 'userFileParser',
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $users = $form['file']->getData();

                if (!$form->get('verifier')->isClicked()) {
                    $this->getDoctrine()->getManager()->flush();

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        count($users).' utilisateurs ajoutés.'
                    );

                    return $this->redirect($this->generateUrl('pjm_app_admin_users_inscriptionListe'));
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Aucun ajout n'a été fait. Il y a des erreurs dans le fichier."
                );
            }
        }

        return $this->render('PJMAppBundle:Admin:users_new_users.html.twig', array(
            'form' => $form->createView(),
            'users' => isset($users) ? $users : [],
        ));
    }

    public function inscriptionUniqueAction(Request $request)
    {
        $userManager = $this->get('pjm.services.user_manager');
        $userManager->setMailer($this->get('pjm.services.mailer'));
        $user = $userManager->createUser();

        $form = $this->createForm(new NewUserType(), $user, array(
            'action' => $this->generateUrl('pjm_app_admin_users_inscriptionUnique'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->configure($user, true);
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add(
                'success',
                'Utilisateur ajouté.'
            );

            return $this->redirect($this->generateUrl('pjm_app_admin_users_inscriptionUnique'));
        }

        return $this->render('PJMAppBundle:Admin:users_new_user.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
