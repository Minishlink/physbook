<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\Vacances;
use PJM\AppBundle\Form\Type\VacancesType;
use PJM\AppBundle\Form\Type\Consos\PrixBaguetteType;

class BragsAdminController extends Controller
{
    private $slug;
    private $itemSlug;

    public function __construct()
    {
        $this->slug = 'brags';
        $this->itemSlug = 'baguette';
    }

    public function indexAction()
    {
        return $this->render('PJMAppBundle:Admin:Consos/Brags/index.html.twig', array(
            'boquetteSlug' => $this->slug,
        ));
    }

    public function listeCommandesAction()
    {
        $datatable = $this->get('pjm.datatable.commandes');
        $datatable->buildDatatableView();

        $commandes = $this->getDoctrine()->getManager()
            ->getRepository('PJMAppBundle:Commande')
            ->getCommandesParEtages();

        return $this->render('PJMAppBundle:Admin:Consos/Brags/listeCommandes.html.twig', array(
            'datatable' => $datatable,
            'commandes' => $commandes,
        ));
    }

    // action ajax de rendu de la liste des commandes
    public function commandesResultsAction()
    {
        $datatable = $this->get('sg_datatables.datatable')->getDatatable($this->get('pjm.datatable.commandes'));
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Commande');
        $datatable->addWhereBuilderCallback($repository->callbackFindByItemSlug($this->itemSlug));

        return $datatable->getResponse();
    }

    public function validerCommandesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $listeCommandes = $request->request->get('data');
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('PJMAppBundle:Commande');

            foreach ($listeCommandes as $commandeChoice) {
                $commande = $repository->find($commandeChoice['value']);

                if ($commande->getItem()->getSlug() == $this->itemSlug && null === $commande->getValid()) {
                    $commandes = $repository->findByUserAndItemSlug($commande->getUser(), $this->itemSlug);

                    // on résilie les précédentes commandes
                    foreach ($commandes as $c) {
                        if ($c != $commande && (null === $c->getValid() || $c->getValid() === true)) {
                            $c->resilier();
                            $em->persist($c);
                        }
                    }

                    if ($commande->getNombre() != 0) {
                        // on valide la commande demandée
                        $commande->valider();

                        // on met à jour le prix de la commande car il pourrait avoir changé
                        $commande->setItem($this->get('pjm.services.boquette.brags')->getCurrentBaguette());

                        $em->persist($commande);
                    } else {
                        // si c'est une demande de résiliation on supprime pour pas embrouiller l'historique
                        $em->remove($commande);
                    }
                }
            }

            $em->flush();

            return new Response('Ok');
        }

        return $this->redirect($this->generateUrl('pjm_app_homepage'));
    }

    public function resilierCommandesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $listeCommandes = $request->request->get('data');
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('PJMAppBundle:Commande');

            foreach ($listeCommandes as $commandeChoice) {
                $commande = $repository->find($commandeChoice['value']);

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

            return new Response('Ok');
        }

        return $this->redirect($this->generateUrl('pjm_app_homepage'));
    }

    public function listeVacancesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $vacances = new Vacances();

        $form = $this->createForm(new VacancesType(), $vacances, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_admin_boquette_brags_listeVacances'),
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

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_boquette_brags_index'));
        }

        $datatable = $this->get('pjm.datatable.vacances');
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:Consos/Brags/listeVacances.html.twig', array(
            'form' => $form->createView(),
            'datatable' => $datatable,
        ));
    }

    public function vacancesResultsAction()
    {
        $datatable = $this->get('sg_datatables.datatable')->getDatatable($this->get('pjm.datatable.vacances'));

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

        return $this->redirect($this->generateUrl('pjm_app_admin_boquette_brags_index'));
    }

    public function listePrixAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Item');

        $nouveauPrix = new Item();
        $nouveauPrix->setLibelle('Baguette de pain');
        $nouveauPrix->setBoquette($this->get('pjm.services.boquette.brags')->getBoquette());
        $nouveauPrix->setSlug($this->itemSlug);

        $currentBaguette = $this->get('pjm.services.boquette.brags')->getCurrentBaguette();

        $form = $this->createForm(new PrixBaguetteType(), $nouveauPrix, array(
            'action' => $this->generateUrl('pjm_app_admin_boquette_brags_listePrix'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on met à jour le prix
                $ancienPrix = $currentBaguette;
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

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_boquette_brags_index'));
        }

        $datatable = $this->get('pjm.datatable.prix');
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:Consos/Brags/listePrix.html.twig', array(
            'datatable' => $datatable,
            'prixActuel' => $currentBaguette->getPrix(),
            'form' => $form->createView(),
        ));
    }

    public function prixResultsAction()
    {
        $datatable = $this->get('sg_datatables.datatable')->getDatatable($this->get('pjm.datatable.prix'));
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Item');
        $datatable->addWhereBuilderCallback($repository->callbackFindBySlug($this->itemSlug));

        return $datatable->getResponse();
    }
}
