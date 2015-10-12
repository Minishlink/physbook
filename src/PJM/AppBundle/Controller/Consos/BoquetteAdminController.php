<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\QueryBuilder;
use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\Responsable;
use PJM\AppBundle\Entity\FeaturedItem;
use PJM\AppBundle\Form\Type\Consos\TransactionType;
use PJM\AppBundle\Form\Type\Admin\ResponsableType;
use PJM\AppBundle\Form\Type\Admin\FeaturedItemType;
use PJM\AppBundle\Form\Type\Admin\ItemType;
use PJM\AppBundle\Form\Type\Filter\TransactionFilterType;
use PJM\AppBundle\Form\Type\Filter\CompteFilterType;
use PJM\AppBundle\Entity\Consos\Transfert;

class BoquetteAdminController extends Controller
{
    /**
     * Page par défaut d'admin des boquettes.
     *
     * @param object   Boquette $boquette
     */
    public function defaultAdminAction(Boquette $boquette)
    {
        return $this->render('PJMAppBundle:Admin:Boquette/default.html.twig', array(
            'boquette' => $boquette,
        ));
    }

    /**
     * Gère la liste des crédits et opérations (débits) pour une boquette.
     */
    public function gestionCreditsAction(Request $request, Boquette $boquette)
    {
        $credit = new Transaction();

        $form = $this->createForm(new TransactionType(), $credit, array(
            'method' => 'POST',
            'action' => $this->generateUrl(
                'pjm_app_admin_boquette_gestionCredits',
                array('slug' => $boquette->getSlug())
            ),
            'boquette' => $boquette,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on enregistre le crédit dans l'historique
                $credit->setStatus('OK');
                $retour = $this->get('pjm.services.transaction_manager')->traiter($credit);

                if ($credit->getStatus() == 'OK') {
                    if ($credit->getMoyenPaiement() != 'operation') {
                        $request->getSession()->getFlashBag()->add(
                            'success',
                            'La transaction a été enregistrée et le compte a été crédité.'
                        );
                    } else {
                        $request->getSession()->getFlashBag()->add(
                            'success',
                            'La transaction a été enregistrée et le compte a été débité.'
                        );
                    }
                } else {
                    // si une erreur est survenue pendant le process de mise à jour du compte
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        'Un problème est survenu lors de la transaction, note bien le code d\'erreur : '.$credit->getStatus()
                    );
                }

                if ($retour instanceof Transfert) {
                    if ($retour->getStatus() == 'OK') {
                        $request->getSession()->getFlashBag()->add(
                            'success',
                            'Le transfert a été effectué.'
                        );
                    } else {
                        // si une erreur est survenue pendant le process de mise à jour du compte
                        $request->getSession()->getFlashBag()->add(
                            'warning',
                            'Le transfert n\'a pas été effectué (code d\'erreur : '.$retour->getStatus().').'
                        );
                    }
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de la transaction. Réessaye.'
                );

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_boquette_'.$boquette->getSlug().'_index'));
        }

        $datatable = $this->get('pjm.datatable.credits');
        $datatable->setAdmin(true);
        $datatable->setAjaxUrl($this->generateUrl(
            'pjm_app_admin_boquette_creditsResults',
            array('boquette_slug' => $boquette->getSlug())
        ));
        $datatable->buildDatatable();

        return $this->render('PJMAppBundle:Admin:Consos/gestionCredits.html.twig', array(
            'form' => $form->createView(),
            'datatable' => $datatable,
            'boquetteSlug' => $boquette->getSlug(),
        ));
    }

    /**
     * Exporte un Excel de crédits.
     *
     * @param object Boquette $boquette La boquette en question
     */
    public function exportCreditsAction(Request $request, Boquette $boquette)
    {
        $filterForm = $this->createForm(new TransactionFilterType(), null, array(
            'method' => 'GET',
            'action' => $this->generateUrl('pjm_app_admin_boquette_exportCredits', array(
                'slug' => $boquette->getSlug(),
            )),
        ));
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted()) {
            if ($filterForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('PJMAppBundle:Transaction');

                $filterBuilder = $repository->buildFindByBoquette($boquette, null, 'notNull');
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $filterBuilder);

                $credits = $filterBuilder->getQuery()->getResult();

                if ($credits !== null) {
                    $tableau = array();
                    foreach ($credits as $credit) {
                        $tableau[] = $credit->toArray();
                    }

                    $excel = $this->get('pjm.services.excel');
                    $excel->create(
                        '['.$boquette->getNomCourt().'] Crédits au '.date('d/m/Y')
                    );

                    $entetes = array(
                        'Date',
                        'Username',
                        'Prénom',
                        'Nom',
                        'Montant',
                        'Type',
                        'Infos',
                        'Statut',
                    );

                    $excel->setData($entetes, $tableau, 'A', '1', 'Crédits');

                    return $excel->download(
                        'credits-'.$boquette->getSlug().'-'.date('d/m/Y')
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_boquette_default', array('slug' => $boquette->getSlug())));
        }

        return $this->render('PJMAppBundle:Admin:Consos/export.html.twig', array(
            'formFilter' => $filterForm->createView(),
        ));
    }

    /**
     * Action ajax de rendu de la liste des crédits d'une boquette.
     */
    public function creditsResultsAction($boquette_slug)
    {
        $datatable = $this->get('pjm.datatable.credits');
        $datatable->setAdmin(true);
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Transaction');
        $query->addWhereAll($repository->callbackFindByBoquetteSlug($boquette_slug));

        return $query->getResponse();
    }

    /**
     * Gère la liste des responsables pour une boquette. (ajout et liste).
     */
    public function gestionResponsablesAction(Request $request, Boquette $boquette)
    {
        $utils = $this->get('pjm.services.utils');
        $responsable = new Responsable();

        $form = $this->createForm(new ResponsableType(), $responsable, array(
            'method' => 'POST',
            'action' => $this->generateUrl(
                'pjm_app_admin_boquette_gestionResponsables',
                array(
                    'slug' => $boquette->getSlug(),
                )
            ),
            'boquette' => $boquette,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$utils->estNiveauUn($this->getUser(), $boquette)) {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Il faut que tu ait un niveau hiérarchique plus haut pour faire cette action.'
                );
            } else {
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($responsable);

                    $userManager = $this->get('fos_user.user_manager');
                    $user = $responsable->getUser();
                    $role = $responsable->getResponsabilite()->getRole();

                    if (!empty($role) && !$user->hasRole($role) && $responsable->getActive()) {
                        $user->addRole($role);
                    }

                    $userManager->updateUser($user);

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        $user.' est indiqué '.$responsable->getResponsabilite()->getLibelle().' dans '.$boquette.'.'
                    );
                } else {
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        'Un problème est survenu lors de la modification du responsable. Réessaye. Vérifie que le profil de l\'utilisateur est complet.'
                    );

                    foreach ($form->getErrors() as $error) {
                        $request->getSession()->getFlashBag()->add(
                            'warning',
                            $error->getMessage()
                        );
                    }
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_boquette_default', array('slug' => $boquette->getSlug())));
        }

        $datatable = $this->get('pjm.datatable.admin.responsable');
        $datatable->setBoquetteSlug($boquette->getSlug());
        $datatable->buildDatatable();

        return $this->render('PJMAppBundle:Admin:gestionResponsables.html.twig', array(
            'form' => $form->createView(),
            'datatable' => $datatable,
        ));
    }

    /**
     * Action ajax de rendu de la liste des responsables d'une boquette.
     */
    public function responsablesResultsAction($boquette_slug)
    {
        $datatable = $this->get('pjm.datatable.admin.responsable');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $query->addWhereAll(
            function (QueryBuilder $qb) use ($boquette_slug) {
                $qb
                    ->join('responsable.responsabilite', 're')
                    ->join('re.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
                    ->setParameter(':boquette_slug', $boquette_slug)
                ;
            }
        );

        return $query->getResponse();
    }

    /**
     * Action ajax d'activation ou désactivation des responsables.
     */
    public function toggleResponsablesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $listeResponsables = $request->request->get('data');

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('PJMAppBundle:Responsable');

            foreach ($listeResponsables as $responsableChoice) {
                $responsable = $repository->find($responsableChoice['value']);
                $responsable->toggleActive();
                $em->persist($responsable);
            }

            $em->flush();

            return new Response('Responsables toggled.');
        }

        return $this->redirect($this->generateUrl('pjm_app_homepage'));
    }

    /**
     * Affiche la liste des comptes des PGs d'une boquette.
     */
    public function voirComptesAction(Boquette $boquette)
    {
        $datatable = $this->get('pjm.datatable.admin.consos.comptes');
        $datatable->setBoquetteSlug($boquette->getSlug());
        $datatable->buildDatatable();

        return $this->render('PJMAppBundle:Admin:Consos/comptes.html.twig', array(
            'datatable' => $datatable,
            'boquette' => $boquette,
        ));
    }

    /**
     * Exporte un Excel des comptes des PGs d'une boquette.
     *
     * @param object Boquette $boquette La boquette en question
     */
    public function exportComptesAction(Request $request, Boquette $boquette)
    {
        $filterForm = $this->createForm(new CompteFilterType(), null, array(
            'method' => 'GET',
            'action' => $this->generateUrl('pjm_app_admin_boquette_exportComptes', array(
                'slug' => $boquette->getSlug(),
            )),
        ));
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted()) {
            if ($filterForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('PJMAppBundle:Compte');

                $filterBuilder = $repository->buildFindByBoquette($boquette);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $filterBuilder);

                $comptes = $filterBuilder->getQuery()->getResult();

                if ($comptes !== null) {
                    $tableau = array();
                    foreach ($comptes as $compte) {
                        $tableau[] = $compte->toArray();
                    }

                    $excel = $this->get('pjm.services.excel');
                    $excel->create(
                        '['.$boquette->getNomCourt().'] Comptes au '.date('d/m/Y')
                    );

                    $entetes = array(
                        'Username',
                        'Prénom',
                        'Nom',
                        "K'gib",
                        'Solde',
                    );

                    $excel->setData($entetes, $tableau, 'A', '1', 'Comptes');

                    return $excel->download(
                        'comptes-'.$boquette->getSlug().'-'.date('d/m/Y')
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_boquette_default', array('slug' => $boquette->getSlug())));
        }

        return $this->render('PJMAppBundle:Admin:Consos/export.html.twig', array(
            'formFilter' => $filterForm->createView(),
        ));
    }

    /**
     * Action ajax de rendu de la liste des comptes des PGs d'une boquette.
     */
    public function comptesResultsAction($boquette_slug)
    {
        $datatable = $this->get('pjm.datatable.admin.consos.comptes');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Compte');
        $query->addWhereAll($repository->callbackFindByBoquetteSlug($boquette_slug));

        return $query->getResponse();
    }

    /**
     * Gestion des items pour une boquette.
     */
    public function gestionItemAction(Request $request, Boquette $boquette)
    {
        $datatable = $this->get('pjm.datatable.boquette.item');
        $datatable->setBoquetteSlug($boquette->getSlug());
        $datatable->setAdmin(true);
        $datatable->buildDatatable();

        return $this->render('PJMAppBundle:Admin:listeItem.html.twig', array(
            'datatable' => $datatable,
        ));
    }

    /**
     * Modifier un item.
     *
     * @ParamConverter("boquette", options={"mapping": {"boquette": "slug"}})
     */
    public function modifierItemAction(Request $request, Boquette $boquette, Item $item)
    {
        $form = $this->createForm(new ItemType(), $item, array(
            'method' => 'POST',
            'action' => $this->generateUrl(
                'pjm_app_admin_boquette_modifierItem',
                array(
                    'boquette' => $boquette->getSlug(),
                    'item' => $item->getId(),
                )
            ),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($item);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    "L'image de l'item ".$item.' a été modifiée.'
                );

                return $this->redirect($this->generateUrl('pjm_app_admin_boquette_default', array('slug' => $boquette->getSlug())));
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Un problème est survenu lors de la modification de l'image de l'item ".$item.'. Réessaye.'
                );

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }
        }

        return $this->render('PJMAppBundle:Admin:imageItem.html.twig', array(
            'form' => $form->createView(),
            'item' => $item,
        ));
    }

    /**
     * Gère la liste des produits mis en avant pour une boquette. (ajout et liste).
     */
    public function gestionFeaturedItemAction(Request $request, Boquette $boquette)
    {
        $featuredItem = new FeaturedItem();

        $form = $this->createForm(new FeaturedItemType(), $featuredItem, array(
            'method' => 'POST',
            'action' => $this->generateUrl(
                'pjm_app_admin_boquette_gestionFeaturedItem',
                array(
                    'slug' => $boquette->getSlug(),
                )
            ),
            'boquette' => $boquette,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                // on désactive l'ancien FeaturedItem
                $oldFeaturedItem = $em
                    ->getRepository('PJMAppBundle:FeaturedItem')
                    ->findByBoquetteSlug($boquette->getSlug(), true);

                if (isset($oldFeaturedItem)) {
                    $oldFeaturedItem->setActive(false);
                    $em->persist($oldFeaturedItem);
                }

                // on active le nouveau
                $em->persist($featuredItem);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    "L'item ".$featuredItem->getItem().' est maintenant mis en avant.'
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Un problème est survenu lors de la mise en avant de l'item ".$featuredItem->getItem().'. Réessaye.'
                );

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_boquette_'.$boquette->getSlug().'_index'));
        }

        $datatable = $this->get('pjm.datatable.admin.featuredItem');
        $datatable->setBoquetteSlug($boquette->getSlug());
        $datatable->buildDatatable();

        return $this->render('PJMAppBundle:Admin:gestionFeaturedItem.html.twig', array(
            'form' => $form->createView(),
            'datatable' => $datatable,
        ));
    }

    /**
     * Action ajax de rendu de la liste des produits mis en avant.
     */
    public function featuredItemResultsAction($boquette_slug)
    {
        $datatable = $this->get('pjm.datatable.admin.featuredItem');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:FeaturedItem');
        $query->addWhereAll($repository->callbackFindByBoquetteSlug($boquette_slug));

        return $query->getResponse();
    }

    /**
     * Affiche la liste des achats.
     *
     * @param object Boquette $boquette
     *
     * @return object Template
     */
    public function voirAchatsAction(Boquette $boquette)
    {
        $datatable = $this->get('pjm.datatable.achats');
        $datatable->setAjaxUrl($this->generateUrl(
            'pjm_app_admin_boquette_achatsResults',
            array('boquette_slug' => $boquette->getSlug())
        ));
        $datatable->setAdmin(true);
        $datatable->buildDatatable();

        return $this->render('PJMAppBundle:Admin:Consos/achats.html.twig', array(
            'datatable' => $datatable,
            'boquette' => $boquette,
        ));
    }

    /**
     * Action ajax de rendu de la liste des achats d'une boquette.
     */
    public function achatsResultsAction($boquette_slug)
    {
        $datatable = $this->get('pjm.datatable.achats');
        $datatable->setAdmin(true);
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Historique');
        $query->addWhereAll($repository->callbackFindByBoquetteSlug($boquette_slug));

        return $query->getResponse();
    }
}
