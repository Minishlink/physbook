<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\ORM\EntityRepository;

use PJM\AppBundle\Entity\Commande;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Form\Consos\CommandeType;
use PJM\AppBundle\Form\Consos\PrixBaguetteType;

class BragsController extends Controller
{
    private $slug;

    public function __construct()
    {
        $this->slug = 'brags';
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

        $repository = $em->getRepository('PJMAppBundle:Historique');
        $commandes = $repository->findByUserAndItemSlug($this->getUser(), 'baguette');

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

    public function getSolde()
    {
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('PJMAppBundle:Boquette');
        $boquette = $repository->findOneBySlug($this->slug);

        $repository = $em->getRepository('PJMAppBundle:Compte');
        $compte = $repository->findOneByUserAndBoquette($this->getUser(), $boquette);

        if ($compte === null) {
            $solde = 0;
        } else {
            $solde = $compte->getSolde();
        }

        return $solde;
    }

    public function getPrixBaguette()
    {
        $em = $this->getDoctrine()->getManager();
        $prixBaguette = $em->getRepository('PJMAppBundle:Item')
            ->findOneBySlugAndValid('baguette', true)
            ->getPrix();

        return $prixBaguette;
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
        $form = $this->createFormBuilder()
            ->add('montant', 'money', array(
                'error_bubbling' => true,
                'constraints' => array(
                new NotBlank(),
                new Range(array(
                    'min' => 1,
                    'minMessage' => 'Le montant doit être supérieur à 1€.',
                )),
            )))
            ->setMethod('POST')
            ->setAction($this->generateUrl('pjm_app_consos_brags_rechargement'))
            ->getForm();

        $form->handleRequest($request);
        $data = $form->getData();
        $montant = $data['montant'];

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on redirige vers S-Money
                $resRechargement = json_decode(
                    $this->forward('PJMAppBundle:Consos/Rechargement:getURL', array(
                        'montant' => $montant*100,
                        'boquette_slug' => $this->slug
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
                    'Un problème est survenu lors de ton rechargement. Réessaye.'
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
            'action' => $this->generateUrl('pjm_app_consos_brags_commande'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $item = $em->getRepository('PJMAppBundle:Item')
                    ->findOneBySlug('baguette');
                $commande->setItem($item);
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

        return $this->render('PJMAppBundle:Consos:Brags/Admin/index.html.twig');
    }

    public function listeCommandesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Commande');
        $commandes = $repository->findByItemSlug('baguette');

        return $this->render('PJMAppBundle:Consos:Brags/Admin/listeCommandes.html.twig', array(
            'commandes' => $commandes
        ));
    }

    public function validerCommandeAction(Request $request, Historique $commande)
    {
        // TODO listener envoi d'email de notification
        // TODO sélectionner commandes et faire une action globale
        if ($commande->getItem()->getSlug() == "baguette" && null === $commande->getValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('PJMAppBundle:Historique');
            $commandes = $repository->findByUserAndItemSlug($commande->getUser(), 'baguette');

            foreach ($commandes as $c) {
                if ($c->getValid() === true) {
                    $request->getSession()->getFlashBag()->add(
                        'info',
                        'La commande #'.$commande->getId().' ('.($commande->getNombre()/10).' baguettes de pain par jour pour '.$commande->getUser()->getUsername().') a été résiliée.'
                    );

                    $c->setValid(false);
                    $em->persist($c);
                }
            }

            $commande->setValid(true);
            $commande->setDateDebut(new \DateTime());
            $em->persist($commande);
            $em->flush();

            $request->getSession()->getFlashBag()->add(
                'success',
                'La commande #'.$commande->getId().' ('.($commande->getNombre()/10).' baguettes de pain par jour pour '.$commande->getUser()->getUsername().') est validée.'
            );

            return $this->redirect($this->generateUrl('pjm_app_consos_brags_admin_index'));
        }

        throw new HttpException(403, 'Cette commande de pain n\'est pas valide.');
    }

    public function resilierCommandeAction(Request $request, Commande $commande)
    {
        if ($commande->getItem()->getSlug() == "baguette"
            && ($commande->getValid() === true || null === $commande->getValid())) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('PJMAppBundle:Commande');
            $commande->setValid(false);
            $commande->setDateFin(new \DateTime());
            $em->persist($commande);
            $em->flush();

            $request->getSession()->getFlashBag()->add(
                'success',
                'La commande #'.$commande->getId().' ('.($commande->getNombre()/10).' baguettes de pain par jour pour '.$commande->getUser()->getUsername().') a été résiliée.'
            );

            return $this->redirect($this->generateUrl('pjm_app_consos_brags_admin_index'));
        }

        throw new HttpException(403, 'Cette commande de pain n\'est pas valide.');
    }

    public function listeBucquagesAction()
    {
        /*$em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Historique');
        $commandes = $repository->findByItemSlug('baguette');*/

        $bucquages = null;

        return $this->render('PJMAppBundle:Consos:Brags/Admin/listeBucquages.html.twig', array(
            'bucquages' => $bucquages
        ));
    }

    public function listeVacancesAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('nbJours', 'number', array(
                'error_bubbling' => true,
                'constraints' => array(
                    new NotBlank(),
            )))
            ->add('date', 'date', array(
                'error_bubbling' => true,
                'constraints' => array(
                    new NotBlank(),
            )))
            ->setMethod('POST')
            ->setAction($this->generateUrl('pjm_app_consos_brags_admin_listeVacances'))
            ->getForm();

        $form->handleRequest($request);
        $data = $form->getData();
        $nbJours = $data['nbJours'];
        $date = $data['date'];

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on va chercher la liste des personnes ayant une commande en cours
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('PJMAppBundle:Commande');
                $nbEleves = 10;

                // on enregistre dans l'historique un crédit à la date donnée

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'Un crédit de '.$nbJours.' jours sera effectué pour '.$nbEleves.' personnes le '.$date->format('d/m/Y').'.'
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

            return $this->redirect($this->generateUrl('pjm_app_consos_brags_admin_index'));
        }

        $bucquages = null;

        return $this->render('PJMAppBundle:Consos:Brags/Admin/listeVacances.html.twig', array(
            'bucquages' => $bucquages,
            'form' => $form->createView()
        ));
    }

    public function listePrixAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Item');

        $nouveauPrix = new Item();
        $nouveauPrix->setLibelle('Baguette de pain');
        $nouveauPrix->setBoquette(
            $em->getRepository('PJMAppBundle:Boquette')
                ->findOneBySlug($this->slug)
        );
        $nouveauPrix->setSlug('baguette');

        $form = $this->createForm(new PrixBaguetteType(), $nouveauPrix, array(
            'action' => $this->generateUrl('pjm_app_consos_brags_admin_listePrix'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $ancienPrix = $repository->findOneBySlugAndValid('baguette', true);
                $ancienPrix->setValid(false);
                $em->persist($ancienPrix);
                $em->persist($nouveauPrix);
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

            return $this->redirect($this->generateUrl('pjm_app_consos_brags_admin_index'));
        }

        $listePrix = $repository->findBySlug('baguette');

        return $this->render('PJMAppBundle:Consos:Brags/Admin/listePrix.html.twig', array(
            'listePrix' => $listePrix,
            'form'      => $form->createView()
        ));
    }

    public function editZiBragsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMUserBundle:User');
        $role = 'ROLE_ZIBRAGS';

        $form = $this->createFormBuilder()
            ->add('user', 'entity', array(
                'error_bubbling' => true,
                'class' => 'PJMUserBundle:User',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC');
                },
                'constraints' => array(
                    new NotBlank(),
            )))
            ->setMethod('POST')
            ->setAction($this->generateUrl('pjm_app_consos_brags_admin_editZiBrags'))
            ->getForm();

        $form->handleRequest($request);
        $data = $form->getData();
        $user = $data['user'];

        $userManager = $this->get('fos_user.user_manager');

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $listeAncienZiBrags = $repository->findByRole($role);

                foreach ($listeAncienZiBrags as $zibrags) {
                    $zibrags->removeRole($role);
                    $userManager->updateUser($zibrags, false);
                }

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
                    'Un problème est survenu lors du changement de ZiBrag\'s. Réessaye. Vérifie que le profil de l\'utilisateur est complet.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_consos_brags_admin_index'));
        }

        $listeZiBrags = $repository->findByRole($role);

        return $this->render('PJMAppBundle:Consos:Brags/Admin/editZiBrags.html.twig', array(
            'form'      => $form->createView(),
            'listeZiBrags' => $listeZiBrags
        ));
    }
}
