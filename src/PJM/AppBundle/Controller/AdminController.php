<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use PJM\AppBundle\Form\Type\Admin\NewUserType;
use PJM\AppBundle\Form\Type\Admin\ResponsabiliteType;
use PJM\AppBundle\Form\Type\Admin\BoquetteType;
use PJM\AppBundle\Entity\Responsabilite;
use PJM\AppBundle\Entity\Compte;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Inbox\Inbox;
use PJM\UserBundle\Entity\User;

class AdminController extends Controller
{
    /**
     * Page d'accueil de la gestion du site
     * @return object Vue de la page
     */
    public function indexAction()
    {
        return $this->render('PJMAppBundle:Admin:index.html.twig');
    }

    /**
     * Gère les responsabilités
     * @param object Request $request Requête
     */
    public function responsabilitesAction(Request $request, Responsabilite $responsabilite = null)
    {
        $ajout = ($responsabilite === null);
        if($ajout) {
            $responsabilite = new Responsabilite();
            $urlAction = $this->generateUrl('pjm_app_admin_responsabilites');
        } else {
            $urlAction = $this->generateUrl('pjm_app_admin_responsabilites', array(
                'responsabilite' => $responsabilite->getId()
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

        $datatable = $this->get("pjm.datatable.admin.responsabilites");
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:responsabilites.html.twig', array(
            'ajout' => $ajout,
            'form' => $form->createView(),
            'datatable' => $datatable
        ));
    }

    /**
     * Va chercher toutes les entités Responsabilite.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function responsabilitesResultsAction()
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.admin.responsabilites"));

        return $datatable->getResponse();
    }

    /**
     * Gère les boquettes
     * @param object Request $request Requête
     */
    public function gestionBoquettesAction(Request $request, Boquette $boquette = null)
    {
        $ajout = ($boquette === null);
        if($ajout) {
            $boquette = new Boquette();
            $urlAction = $this->generateUrl('pjm_app_admin_gestionBoquettes');
        } else {
            $urlAction = $this->generateUrl('pjm_app_admin_gestionBoquettes', array(
                'boquette' => $boquette->getId()
            ));
        }

        $form = $this->createForm(new BoquetteType(), $boquette, array(
            'method' => 'POST',
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

                return $this->gestionBoquettesAction(new Request());
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

        $datatable = $this->get("pjm.datatable.admin.boquettes");
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:gestionBoquettes.html.twig', array(
            'ajout' => $ajout,
            'form' => $form->createView(),
            'datatable' => $datatable
        ));
    }

    /**
     * Va chercher toutes les entités Boquette.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function boquettesResultsAction()
    {
        $datatable = $this->get("pjm.datatable.admin.boquettes");
        $datatable->setExtImage($this->get('pjm.services.image'));
        $datatableData = $this->get("sg_datatables.datatable")->getDatatable($datatable);

        return $datatableData->getResponse();
    }

    public function listeAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        return $this->render('PJMAppBundle:Admin:users_liste.html.twig', array(
            'users' => $users
        ));
    }

    public function inscriptionListeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $form = $this->createFormBuilder(null, array(
            'action' => $this->generateUrl('pjm_app_admin_users_inscriptionListe'),
            'method' => 'POST',
        ))
            ->add('liste', 'file')
            ->add('verifier', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['liste']->getData();

            if ($file->isValid()) {
                $handle = fopen($file, "r");
                $users = array();
                $problem = 0;

                // les boquettes concernées pour l'ouverture de compte :
                $repository = $em->getRepository('PJMAppBundle:Boquette');
                $boquettes = array(
                    $repository->findOneBySlug('pians'),
                    $repository->findOneBySlug('paniers'),
                    $repository->findOneBySlug('brags'),
                );

                while (($data = fgetcsv($handle, 0, "\t")) !== false) {
                    if (count($data) >= 8) {
                        // si il y a au moins le nombre de paramètres requis
                        $user = $userManager->createUser();

                        $user->setFams($data[0]);
                        $user->setTabagns(strtolower($data[1]));
                        $user->setProms($data[2]);
                        $user->setEmail(strtolower($data[3]));
                        $user->setBucque($data[4]);
                        $user->setPlainPassword($data[5]);
                        $user->setPrenom($data[6]);
                        $user->setNom($data[7]);

                        $user->setUsername($user->getFams().$user->getTabagns().$user->getProms());
                        if (!empty($data[8])) {
                            $user->setGenre($data[8] == "F");
                        }

                        if (!empty($data[9])) {
                            $tel = (strlen($data[9]) == 9) ? "0".$data[9] : $data[9];
                            $user->setTelephone($tel);
                        }

                        if (!empty($data[10])) {
                            $user->setAppartement(strtoupper($data[10]));
                        }

                        if (!empty($data[11])) {
                            $user->setClasse(strtoupper($data[11]));
                        }

                        if (!empty($data[12])) {
                            $user->setAnniversaire($data[12]);
                        }

                        $user->setEnabled(true);

                        //on crée l'inbox
                        $inbox = new Inbox();
                        $user->setInbox($inbox);

                        $userManager->updateUser($user, false);
                        $users[] = $user;

                        // on crée les comptes
                        foreach ($boquettes as $boquette) {
                            $nvCompte = new Compte($user, $boquette);
                            $em->persist($nvCompte);
                        }
                    } else {
                        $problem++;
                    }
                }

                fclose($handle);
                $nbUsers = count($users);

                if (!$problem) {
                    if ($nbUsers && !$form->get('verifier')->isClicked()) {
                        $success = true;

                        try {
                            $em->flush();
                        } catch (\Doctrine\DBAL\DBALException $e) {
                            if ($e->getPrevious()->getCode() === '23000') {
                                $success = false;

                                $request->getSession()->getFlashBag()->add(
                                    'danger',
                                    'Erreur : un utilisateur existe déjà !'
                                );

                                $request->getSession()->getFlashBag()->add(
                                    'warning',
                                    $e->getMessage()
                                );
                            } else {
                                throw $e;
                            }
                        }

                        if ($success) {
                            $request->getSession()->getFlashBag()->add(
                                'success',
                                $nbUsers.' utilisateurs ajoutés.'
                            );

                            return $this->redirect($this->generateUrl('pjm_app_admin_users_inscriptionListe'));
                        }
                    }
                } else {
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        "Aucun ajout n'a été fait. Il y a ".$problem." problèmes et ".$nbUsers." utilisateurs corrects."
                    );
                }

                if ($form->get('verifier')->isClicked()) {
                    return $this->render('PJMAppBundle:Admin:users_new_users.html.twig', array(
                        'form' => $form->createView(),
                        'users' => $users
                    ));
                }
            }
        }

        return $this->render('PJMAppBundle:Admin:users_new_users.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function inscriptionUniqueAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $form = $this->createForm(new NewUserType(), $user, array(
            'action' => $this->generateUrl('pjm_app_admin_users_inscriptionUnique'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = substr(uniqid(), 0, 8);
            $user->setPlainPassword($password);
            $user->setUsername($user->getFams().$user->getTabagns().$user->getProms());

            //on crée l'inbox
            $inbox = new Inbox();
            $user->setInbox($inbox);

            $userManager->updateUser($user, false);

            // les boquettes concernées pour l'ouverture de compte :
            $repository = $em->getRepository('PJMAppBundle:Boquette');
            $boquettes = array(
                $repository->findOneBySlug('pians'),
                $repository->findOneBySlug('paniers'),
                $repository->findOneBySlug('brags'),
            );

            // on crée les comptes
            foreach ($boquettes as $boquette) {
                $nvCompte = new Compte($user, $boquette);
                $em->persist($nvCompte);
            }

            try {
                $em->flush();
            } catch (\Doctrine\DBAL\DBALException $e) {
                if ($e->getPrevious()->getCode() === '23000') {
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        'Erreur : l\'utilisateur existe déjà !'
                    );

                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $e->getMessage()
                    );

                    return $this->render('PJMAppBundle:Admin:users_new_user.html.twig', array(
                        'form' => $form->createView(),
                    ));
                } else {
                    throw $e;
                }
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                'Utilisateur ajouté.'
            );

            $this->envoiMailInscription($user, $password);

            return $this->redirect($this->generateUrl('pjm_app_admin_users_inscriptionUnique'));
        }

        return $this->render('PJMAppBundle:Admin:users_new_user.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    private function envoiMailInscription(User $user, $password)
    {
        $utils = $this->get('pjm.services.mailer');

        $context = array(
            "user" => $user,
            "password" => $password,
        );

        $template = 'PJMAppBundle:Mail:inscription.html.twig';

        $utils->send($user, $context, $template);
    }
}
