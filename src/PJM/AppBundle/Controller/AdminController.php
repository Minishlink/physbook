<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use PJM\AppBundle\Form\Admin\NewUserType;

class AdminController extends Controller
{
    public function indexAction()
    {
        return $this->render('PJMAppBundle:Admin:index.html.twig');
    }

    // TODO gérer promos à l'ec'ss ou pas
    public function listeAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        return $this->render('PJMAppBundle:Admin:users_liste.html.twig', array(
            'users' => $users
        ));
    }

    // TODO confirmation (check accents)
    public function inscriptionListeAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $form = $this->createFormBuilder(null, array(
            'action' => $this->generateUrl('pjm_app_admin_users_inscriptionListe'),
            'method' => 'POST',
        ))
            ->add('liste', 'file')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['liste']->getData();

            if ($file->isValid()) {
                $handle = fopen($file, "r");
                $nbUsers = 0;
                $problem = 0;

                while (($data = fgetcsv($handle, 0, "\t")) !== false) {
                    if (count($data) >= 6) {
                        // si il y a au moins le nombre de paramètres requis
                        $user = $userManager->createUser();

                        $user->setFams($data[0]);
                        $user->setTabagns(strtolower($data[1]));
                        $user->setProms($data[2]);
                        $user->setEmail($data[3]);
                        $user->setBucque($data[4]);
                        $user->setPlainPassword($data[5]);
                        $user->setPrenom($data[6]);
                        $user->setNom($data[7]);

                        $user->setUsername($user->getFams().$user->getTabagns().$user->getProms());

                        if (!empty($data[8])) {
                            $tel = (strlen($data[8]) == 9) ? "0".$data[8] : $data[8];
                            $user->setTelephone($tel);
                        }

                        if (!empty($data[9])) {
                            $user->setAppartement(strtoupper($data[9]));
                        }

                        if (!empty($data[10])) {
                            $user->setClasse(strtoupper($data[10]));
                        }

                        if (!empty($data[11])) {
                            $user->setAnniversaire($data[11]);
                        }

                        $user->setEnabled(true);
                        $userManager->updateUser($user, false);
                    } else {
                        $problem++;
                    }
                    $nbUsers++;
                }

                fclose($handle);

                if (!$problem) {
                    if ($nbUsers) {
                        $success = true;

                        try {
                            $this->getDoctrine()->getManager()->flush();
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
                        }
                    }
                } else {
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        "Aucun ajout n'a été fait. Il y a ".$problem." problèmes sur cette liste de ".$nbUsers." utilisateurs."
                    );
                }
            }
        }

        return $this->render('PJMAppBundle:Admin:users_new_users.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function inscriptionUniqueAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $form = $this->createForm(new NewUserType(), $user, array(
            'action' => $this->generateUrl('pjm_app_admin_users_inscriptionUnique'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = uniqid();
            $user->setPassword($password);
            $user->setUsername($user->getFams().$user->getTabagns().$user->getProms());

            // TODO envoyer password par mail

            $userManager->updateUser($user);

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
