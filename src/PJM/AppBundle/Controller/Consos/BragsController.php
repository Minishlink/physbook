<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\ORM\EntityRepository;

use PJM\AppBundle\Entity\Commande;
use PJM\AppBundle\Entity\Historique;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\Vacances;
use PJM\AppBundle\Entity\Compte;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Transaction;
use PJM\UserBundle\Entity\User;
use PJM\AppBundle\Form\VacancesType;
use PJM\AppBundle\Form\Consos\CommandeType;
use PJM\AppBundle\Form\Consos\MontantType;
use PJM\AppBundle\Form\Consos\PrixBaguetteType;

class BragsController extends BoquetteController
{
    private $itemSlug;

    public function __construct()
    {
        $this->slug = 'brags';
        $this->itemSlug = 'baguette';
    }

    public function indexAction(Request $request)
    {
        return $this->render('PJMAppBundle:Consos:Brags/index.html.twig', array(
            'ZiBrags' => $this->getZiBrags(),
            'solde' => $this->getSolde(),
            'prixBaguette' => $this->getPrixBaguette(),
            'commande' => $this->getCommande()
        ));
    }

    public function getCommande()
    {
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('PJMAppBundle:Commande');
        $commandes = $repository->findByUserAndItemSlug($this->getUser(), $this->itemSlug);

        foreach ($commandes as $commande) {
            if (!isset($active) && $commande->getValid()) {
                $active = $commande->getNombre()/10;
            }

            if (!isset($attente) && null === $commande->getValid()) {
                $attente = $commande->getNombre()/10;
            }

            if (isset($active) && isset($attente)) {
                break;
            }
        }

        return array(
            'active' => isset($active) ? $active : 0,
            'attente' => isset($attente) ? $attente : null,
        );
    }

    public function getCurrentBaguette()
    {
        $baguette = $this->getItem($this->itemSlug);

        if (null === $baguette) {
            $baguette = new Item();
            $baguette->setLibelle('Baguette de pain');
            $baguette->setPrix(65);
            $baguette->setSlug($this->itemSlug);
            $baguette->setBoquette($this->getBoquette());
            $baguette->setValid(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($baguette);
            $em->flush();
        }

        return $baguette;
    }

    public function getPrixBaguette()
    {
        return $this->getCurrentBaguette()->getPrix();
    }

    public function getZiBrags($tous = false)
    {
        $em = $this->getDoctrine()->getManager();
        $ZiBrags = $em->getRepository('PJMUserBundle:User')
            ->findByRole('ROLE_ZIBRAGS');

        if ($tous) {
            return $ZiBrags;
        }

        return (isset($ZiBrags[0])) ? $ZiBrags[0] : null;

    }

    public function rechargementAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $transaction->setMoyenPaiement('smoney');

        $form = $this->createForm(new MontantType(), $transaction, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_consos_brags_rechargement'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $boquette = $this->getBoquette();
                $transaction->setBoquette($boquette);
                $transaction->setInfos($boquette->getCaisseSMoney());
                $transaction->setUser($this->getUser());

                // on redirige vers S-Money
                $resRechargement = json_decode(
                    $this->forward('PJMAppBundle:Consos/Rechargement:getURL', array(
                        'transaction' => $transaction,
                    ))->getContent(),
                    true
                );

                if ($resRechargement['valid'] === true) {
                    // succès, on redirige vers l'URL de paiement
                    return $this->redirect($resRechargement['url']);
                } else {
                    // erreur
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        'Il y a eu une erreur lors de la communication avec S-Money.'
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de l\'envoi du formulaire de rechargement. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_consos_brags_index'));
        }

        return $this->render('PJMAppBundle:Consos:Brags/rechargement.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function commandeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $commande = new Commande();

        $form = $this->createForm(new CommandeType(), $commande, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_consos_brags_commande'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $commande->setItem($this->getCurrentBaguette());
                $commande->setUser($this->getUser());
                $commande->setNombre($commande->getNombre()*10);
                $em->persist($commande);
                $em->flush($commande);

                if ($commande->getNombre() > 0) {
                    $request->getSession()->getFlashBag()->add(
                        'success',
                        'Ta commande a été passée. Elle sera validée par le ZiBrag\'s le jour où tu commenceras à pouvoir prendre ton pain. Tu seras notifié.'
                    );
                } else {
                    $request->getSession()->getFlashBag()->add(
                        'success',
                        'Tu ne recevras plus de pain bientôt. Tant que ta résiliation n\'a pas été validée par le ZiBrag\'s, tu peux continuer à prendre ton pain et tu seras débité. Tu seras notifié quand il aura validé  ta résiliation.'
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de ta commande. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_consos_brags_index'));
        }

        return $this->render('PJMAppBundle:Consos:Brags/commande.html.twig', array(
            'form' => $form->createView(),
            'commande' => $this->getCommande()
        ));
    }

    /*
    * ADMIN
    */
    public function adminAction()
    {
        // TODO faire reloguer l'utilisateur sauf si redirection depuis l'admin

        return $this->render('PJMAppBundle:Consos:Brags/Admin/index.html.twig', array(
            'boquetteSlug' => $this->slug
        ));
    }

    public function listeCommandesAction()
    {
        $datatable = $this->get("pjm.datatable.commandes");
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Consos:Brags/Admin/listeCommandes.html.twig', array(
            'datatable' => $datatable
        ));
    }

    // action ajax de rendu de la liste des commandes
    public function commandesResultsAction()
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.commandes"));
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Commande');
        $datatable->addWhereBuilderCallback($repository->callbackFindByItemSlug($this->itemSlug));

        return $datatable->getResponse();
    }

    public function validerCommandesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $listeCommandes = $request->request->get("data");
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository("PJMAppBundle:Commande");
            $repositoryCompte = $em->getRepository('PJMAppBundle:Compte');

            foreach ($listeCommandes as $commandeChoice) {
                $commande = $repository->find($commandeChoice["value"]);

                if ($commande->getItem()->getSlug() == $this->itemSlug && null === $commande->getValid()) {
                    $commandes = $repository->findByUserAndItemSlug($commande->getUser(), $this->itemSlug);

                    // on résilie les précédentes commandes
                    foreach ($commandes as $c) {
                        if ($c != $commande && (null === $c->getValid() || $c->getValid() == true)) {
                            $c->resilier();
                            $em->persist($c);
                        }
                    }

                    if ($commande->getNombre() != 0) {
                        // on valide la commande demandée
                        $commande->valider();

                        // on met à jour le prix de la commande car il pourrait avoir changé
                        $commande->setItem($this->getCurrentBaguette());

                        $em->persist($commande);
                    } else {
                        // si c'est une demande de résiliation on supprime pour pas embrouiller l'historique
                        $em->remove($commande);
                    }
                }
            }

            $em->flush();

            return new Response("This is an ajax response.");
        }

        return new Response("This is not ajax.", 400);
    }

    public function resilierCommandesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $listeCommandes = $request->request->get("data");
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository("PJMAppBundle:Commande");

            foreach ($listeCommandes as $commandeChoice) {
                $commande = $repository->find($commandeChoice["value"]);

                if ($commande->getItem()->getSlug() == $this->itemSlug && $commande->getValid() !== false) {
                    $em = $this->getDoctrine()->getManager();
                    if ($commande->getValid() === true) {
                        $commande->resilier();
                        $em->persist($commande);
                    } elseif (null === $commande->getValid()) {
                        $em->remove($commande);
                    }
                }
            }

            $em->flush();

            return new Response("This is an ajax response.");
        }

        return new Response("This is not ajax.", 400);
    }

    // liste des débits de baguettes
    public function listeBucquagesAction()
    {
        $datatable = $this->get("pjm.datatable.historiqueAdmin");
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Consos:Brags/Admin/listeBucquages.html.twig', array(
            'datatable' => $datatable
        ));
    }

    // action ajax de rendu de la liste des bucquages
    public function bucquagesResultsAction()
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.historiqueAdmin"));
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Historique');
        $datatable->addWhereBuilderCallback($repository->callbackFindByBoquetteSlug($this->slug));

        return $datatable->getResponse();
    }

    public function listeVacancesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $vacances = new Vacances();

        $form = $this->createForm(new VacancesType(), $vacances, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_admin_consos_brags_listeVacances'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($vacances);
                $em->flush();

                if ($vacances->getDateDebut() == $vacances->getDateFin()) {
                    $msg = 'Un jour férié a été enregistré le '.$vacances->getDateDebut()->format('d/m/y').'.';
                } else {
                    $msg = 'Des vacances ont été enregistrées du '.$vacances->getDateDebut()->format('d/m/y').' au '.$vacances->getDateFin()->format('d/m/y').'.';
                }

                $request->getSession()->getFlashBag()->add(
                    'success',
                    $msg
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de l\'envoi de crédit de vacances/jours fériés. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_consos_brags_index'));
        }

        $datatable = $this->get("pjm.datatable.vacances");
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Consos:Brags/Admin/listeVacances.html.twig', array(
            'form' => $form->createView(),
            'datatable' => $datatable
        ));
    }

    public function vacancesResultsAction()
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.vacances"));

        return $datatable->getResponse();
    }

    public function annulerVacancesAction(Request $request, Vacances $vacances)
    {
        if (!$vacances->getFait()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($vacances);
            $em->flush();

            $request->getSession()->getFlashBag()->add(
                'success',
                'Les vacances du '.$vacances->getDateDebut()->format('d/m/y').' au '.$vacances->getDateFin()->format('d/m/y').' ont bien été annulés.'
            );
        } else {
            $request->getSession()->getFlashBag()->add(
                'danger',
                'Les vacances du '.$vacances->getDateDebut()->format('d/m/y').' au '.$vacances->getDateFin()->format('d/m/y').' ne peuvent pas être annulées.'
            );
        }
        return $this->redirect($this->generateUrl('pjm_app_admin_consos_brags_index'));
    }

    public function listePrixAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Item');

        $nouveauPrix = new Item();
        $nouveauPrix->setLibelle('Baguette de pain');
        $nouveauPrix->setBoquette($this->getBoquette());
        $nouveauPrix->setSlug($this->itemSlug);

        $form = $this->createForm(new PrixBaguetteType(), $nouveauPrix, array(
            'action' => $this->generateUrl('pjm_app_admin_consos_brags_listePrix'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on met à jour le prix
                $ancienPrix = $this->getCurrentBaguette();
                $ancienPrix->setValid(false);
                $em->persist($ancienPrix);
                $em->persist($nouveauPrix);

                // on va chercher les commandes en cours
                $repository = $em->getRepository('PJMAppBundle:Commande');
                $commandesActives = $repository->findByItemSlugAndValid($this->itemSlug, true);

                // on les duplique avec le nouveau prix
                foreach ($commandesActives as $oldCommande) {
                    $newCommande = clone $oldCommande;
                    $newCommande->setItem($nouveauPrix);
                    $newCommande->valider();

                    $oldCommande->resilier();

                    $em->persist($newCommande);
                    $em->persist($oldCommande);
                }

                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'Le prix du pain a bien été changé de '.$ancienPrix->getPrix().' à '.$nouveauPrix->getPrix().' cents.'
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors du changement de prix. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_consos_brags_index'));
        }

        $datatable = $this->get('pjm.datatable.prix');
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Consos:Brags/Admin/listePrix.html.twig', array(
            'datatable' => $datatable,
            'prixActuel' => $this->getPrixBaguette(),
            'form'      => $form->createView()
        ));
    }

    public function prixResultsAction()
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.prix"));
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Item');
        $datatable->addWhereBuilderCallback($repository->callbackFindBySlug($this->itemSlug));

        return $datatable->getResponse();
    }

    public function listeZiBragsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMUserBundle:User');
        $role = 'ROLE_ZIBRAGS';

        $form = $this->createFormBuilder()
            ->add('user', 'genemu_jqueryselect2_entity', array(
                'error_bubbling' => true,
                'label' => 'Utilisateur',
                'class' => 'PJMUserBundle:User',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC');
                },
                'constraints' => array(
                    new Assert\NotBlank(),
            )))
            ->add('save', 'submit', array(
                'label' => 'Ajout',
            ))
            ->setMethod('POST')
            ->setAction($this->generateUrl('pjm_app_admin_consos_brags_listeZiBrags'))
            ->getForm();

        $form->handleRequest($request);
        $data = $form->getData();
        $user = $data['user'];

        $userManager = $this->get('fos_user.user_manager');

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (!$user->hasRole($role)) {
                    $user->addRole($role);
                    $userManager->updateUser($user);
                }

                $request->getSession()->getFlashBag()->add(
                    'success',
                    $user.' est maintenant ZiBrag\'s.'
                );

            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de l\'ajout du ZiBrag\'s. Réessaye. Vérifie que le profil de l\'utilisateur est complet.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_consos_brags_index'));
        }

        $listeZiBrags = $repository->findByRole($role);

        return $this->render('PJMAppBundle:Consos:Brags/Admin/listeZiBrags.html.twig', array(
            'form'      => $form->createView(),
            'listeZiBrags' => $listeZiBrags
        ));
    }

    public function removeZiBragsAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMUserBundle:User');
        $role = 'ROLE_ZIBRAGS';
        $userManager = $this->get('fos_user.user_manager');

        if ($user->hasRole($role)) {
            $user->removeRole($role);
            $userManager->updateUser($user);

            $request->getSession()->getFlashBag()->add(
                'success',
                $user.' est maintenant ZiBrag\'s.'
            );
        }

        return $this->redirect($this->generateUrl('pjm_app_admin_consos_brags_index'));
    }

    public function bucquageCronAction()
    {
        $utils = $this->get('pjm.services.utils');
        $msg = $utils->bucquage($this->slug, $this->itemSlug);
        return new Response($msg);
    }
}
