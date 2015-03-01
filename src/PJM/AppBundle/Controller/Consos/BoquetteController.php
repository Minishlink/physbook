<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\Responsable;
use PJM\AppBundle\Entity\FeaturedItem;
use PJM\AppBundle\Form\Consos\TransactionType;
use PJM\AppBundle\Form\Admin\ResponsableType;
use PJM\AppBundle\Form\Consos\MontantType;
use PJM\AppBundle\Form\Admin\FeaturedItemType;
use PJM\AppBundle\Form\Admin\ImageItemType;

class BoquetteController extends Controller
{
    protected $slug;

    public function getTwigTemplatePath($boquetteSlug = null)
    {
        if (isset($boquetteSlug)) {
            $this->slug = $boquetteSlug;
        }

        switch ($this->slug) {
            case 'pians':
                $templatePath = 'PJMAppBundle:Consos/Pians:';
                break;
            case 'cvis':
                $templatePath = 'PJMAppBundle:Consos/Cvis:';
                break;
            case 'brags':
                $templatePath = 'PJMAppBundle:Consos/Brags:';
                break;
            case 'paniers':
                $templatePath = 'PJMAppBundle:Consos/Paniers:';
                break;
            default:
                $templatePath = 'PJMAppBundle::';
                break;
        }

        return $templatePath;
    }

    public function getSolde($user = null)
    {
        if (!isset($user)) {
            $user = $this->getUser();
        }

        $utils = $this->get('pjm.services.utils');
        return $utils->getSolde($user, $this->slug);
    }

    public function getBoquette()
    {
        $utils = $this->get('pjm.services.utils');
        return $utils->getBoquette($this->slug);
    }

    public function getItem($itemSlug, $valid = true)
    {
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('PJMAppBundle:Item');

        if ($valid === 'any') {
            return $rep->findOneBySlug($itemSlug);
        }

        return $rep->findOneBy(array('slug' => $itemSlug, 'valid' => $valid));
    }

    public function getLastItem($itemSlug, $valid = true)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('PJMAppBundle:Item');

        if ($valid === 'any') {
            $res = $repo->findBy(array('slug' => $itemSlug), array('date' => 'DESC'), 1);
        } else {
            $repo->findBy(array('slug' => $itemSlug, 'valid' => $valid), array('date' => 'DESC'), 1);
        }

        if (empty($res)) {
            return null;
        }

        return $res[0];
    }

    public function getItems($valid = true, $limit = null, $offset = null)
    {
        $em = $this->getDoctrine()->getManager();
        $items = $em
            ->getRepository('PJMAppBundle:Item')
            ->findByBoquetteSlug($this->slug, $valid, $limit, $offset);

        return $items;
    }

    public function compterAchatsItem($itemSlug)
    {
        $em = $this->getDoctrine()->getManager();
        $nb = $em->getRepository('PJMAppBundle:Historique')
            ->countByItemSlug($itemSlug);

        return $nb;
    }

    public function compterAchatsBoquette()
    {
        $em = $this->getDoctrine()->getManager();
        $nb = $em->getRepository('PJMAppBundle:Historique')
            ->countByBoquetteSlug($this->slug);

        return $nb;
    }

    /**
     * Cherche les utilisateurs avec le plus d'achats
     * @param  integer [$month = null] Mois à chercher. Si $year null, mois de l'année en cours, sinon année $year. Si $month est null, se réferer à year.
     * @param  integer [$year  = null]  Année à chercher. Si $year null, cherche depuis toujours.
     * @return array   Matrice d'utilisateurs avec leur nombre d'achats associés, triés par ce nombre.
     */
    public function getTopConsommateurs($month = null, $year = null) {
        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('PJMAppBundle:Historique')
            ->getTopUsers($this->slug, 3, $month, $year);

        $topConsommateurs = array();
        foreach ($res as $row) {
            $topConsommateurs[] = array(
                'user' => $row[0]->getUser(),
                'somme' => $row["somme"]/10
            );
        }

        return $topConsommateurs;
    }

    /*
     * Historique des crédits et débits
     */
    public function historiqueAction(Boquette $boquette)
    {
        // on récupère l'historique complet
        $utils = $this->get('pjm.services.utils');
        $historique = $utils->getHistorique($this->getUser(), $boquette->getSlug());

        // outil de sérialisation (conversion de la liste des objets Historique en tableau json)
        $serializer = new Serializer(
            array(new GetSetMethodNormalizer()),
            array(new JsonEncoder())
        );

        // on crée le tableau de données
        $datatable = $this->get('pjm.datatable.boquette.historique');
        $datatable->buildDatatableView();
        $datatable->setData($serializer->serialize($historique, 'json'));

        $layout = $this->getTwigTemplatePath($boquette->getSlug()).'layout.html.twig';
        $routeRetour = 'pjm_app_consos_'.$boquette->getSlug().'_index';

        return $this->render('PJMAppBundle::datatable.html.twig', array(
            'titre' => 'Historique',
            'layout' => $layout,
            'routeRetour' => $routeRetour,
            'datatable' => $datatable
        ));
    }

    /**
     * Affiche un bouton Phy's HM pour un item donné
     */
    public function itemHMAction(Request $request, Item $item)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder($item)
            ->setAction($this->generateUrl(
                "pjm_app_boquette_itemHM",
                array('item' => $item->getId())
            ))
            ->setMethod('POST')
            ->add('save', 'submit', array(
                'label' => "Phy's HM",
                'attr' => array(
                    'class' => 'physHM',
                    'title' => "Phy's HM"
                ),
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (!$item->getUsersHM()->contains($this->getUser())) {
                    $item->addUserHM($this->getUser());
                    $em->persist($item);
                    $em->flush();
                } else {
                     $request->getSession()->getFlashBag()->add(
                        'danger',
                        'Tu as déjà été déclaré "Phy\'s HM" sur cet item.'
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de ton "Phy\'s HM". Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl("pjm_app_consos_".$item->getBoquette()->getSlug()."_index"));
        }


        return $this->render('PJMAppBundle:Boquette:itemHM.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * [ADMIN] Gère la liste des crédits pour une boquette.
     */
    public function gestionCreditsAction(Request $request, Boquette $boquette)
    {
        $em = $this->getDoctrine()->getManager();

        $credit = new Transaction();

        $form = $this->createForm(new TransactionType(), $credit, array(
            'method' => 'POST',
            'action' => $this->generateUrl(
                "pjm_app_admin_consos_gestionCredits",
                array('slug' => $boquette->getSlug())
            )
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on enregistre le crédit dans l'historique
                $credit->setStatus("OK");
                $credit->setBoquette($boquette);
                $em->persist($credit); // le compte est automatiquement mis à jour (TransactionListener)
                $em->flush();

                if ($credit->getStatus() == "OK") {
                    $request->getSession()->getFlashBag()->add(
                        'success',
                        'La transaction a été enregistrée et le compte a été crédité.'
                    );
                } else {
                    // si une erreur est survenue pendant le process de mise à jour du compte
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        'Un problème est survenu lors de la transaction, note bien le code d\'erreur : '.$credit->getStatus()
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de la transaction. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl("pjm_app_admin_consos_".$boquette->getSlug()."_index"));
        }

        $datatable = $this->get("pjm.datatable.credits");
        $datatable->setAjaxUrl($this->generateUrl(
            "pjm_app_admin_consos_creditsResults",
            array('boquette_slug' => $boquette->getSlug())
        ));
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:Consos/gestionCredits.html.twig', array(
            'form' => $form->createView(),
            'datatable' => $datatable
        ));
    }

    /**
     * [ADMIN] Action ajax de rendu de la liste des crédits d'une boquette.
     */
    public function creditsResultsAction($boquette_slug)
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.credits"));
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Transaction');
        $datatable->addWhereBuilderCallback($repository->callbackFindByBoquetteSlugAndValid($boquette_slug));

        return $datatable->getResponse();
    }

    /**
     * [ADMIN] Gère la liste des responsables pour une boquette. (ajout et liste)
     */
    public function gestionResponsablesAction(Request $request, Boquette $boquette)
    {
        $responsable = new Responsable();

        $form = $this->createForm(new ResponsableType(), $responsable, array(
            'method' => 'POST',
            'action' => $this->generateUrl(
                'pjm_app_admin_gestionResponsables',
                array(
                    'slug' => $boquette->getSlug()
                )
            ),
            'boquette' => $boquette
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($responsable);

                $userManager = $this->get('fos_user.user_manager');
                $user = $responsable->getUser();
                $role = $responsable->getResponsabilite()->getRole();

                if ($responsable->getActive()) {
                    if (!$user->hasRole($role)) {
                        $user->addRole($role);
                        $userManager->updateUser($user, false);
                    }

                    $em->flush();

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        $user.' est maintenant '.$responsable->getResponsabilite().' dans '.$boquette.'.'
                    );
                } else {
                    if ($user->hasRole($role)) {
                        $user->removeRole($role);
                        $userManager->updateUser($user, false);
                    }

                    $em->flush();

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        $user.' n\'est plus '.$responsable->getResponsabilite().' dans '.$boquette.'.'
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de la modification du responsable. Réessaye. Vérifie que le profil de l\'utilisateur est complet.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl("pjm_app_admin_consos_".$boquette->getSlug()."_index"));
        }

        $datatable = $this->get("pjm.datatable.admin.responsable");
        $datatable->setBoquetteSlug($boquette->getSlug());
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:gestionResponsables.html.twig', array(
            'form'      => $form->createView(),
            'datatable' => $datatable
        ));
    }

    /**
     * [ADMIN] Action ajax de rendu de la liste des responsables d'une boquette.
     */
    public function responsablesResultsAction($boquette_slug)
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.admin.responsable"));

        $datatable->addWhereBuilderCallback(
            function($qb) use ($boquette_slug) {
                $qb
                    ->join('Responsable.responsabilite', 're')
                    ->join('re.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
                    ->setParameter(":boquette_slug", $boquette_slug)
                ;
            }
        );

        return $datatable->getResponse();
    }

    /**
     * Liste des items pour une boquette.
     */
    public function listeItemAction(Request $request, Boquette $boquette)
    {
        $datatable = $this->get("pjm.datatable.boquette.item");
        $datatable->setBoquetteSlug($boquette->getSlug());
        $datatable->setAdmin(false);
        $datatable->buildDatatableView();

        $layout = $this->getTwigTemplatePath($boquette->getSlug()).'layout.html.twig';
        $routeRetour = 'pjm_app_consos_'.$boquette->getSlug().'_index';

        return $this->render('PJMAppBundle::datatable.html.twig', array(
            'titre' => 'Catalogue',
            'layout' => $layout,
            'routeRetour' => $routeRetour,
            'datatable' => $datatable
        ));
    }

    /**
     * Action ajax de rendu de la liste des items pour DataTables
     */
    public function itemResultsAction($boquette_slug)
    {
        $datatable = $this->get("pjm.datatable.boquette.item");
        $datatable->setTwigExt($this->get('pjm.twig.intranet_extension'));
        $datatable->setBoquetteSlug($boquette_slug);
        $datatableData = $this->get("sg_datatables.datatable")->getDatatable($datatable);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Item');

        $datatableData->addWhereBuilderCallback($repository->callbackFindByBoquetteSlug($boquette_slug));

        return $datatableData->getResponse();
    }

    public function getItemsAction(Request $request, $boquette_slug, $offset = 0)
    {
        if ($request->isXmlHttpRequest()) {
            if (!isset($this->slug)) {
                $this->slug = $boquette_slug;
            }

            return $this->render('PJMAppBundle:Consos/Cvis:produits.html.twig', array(
                'listeProduits' => $this->getItems(true, null, $offset),
                'ajoutCatalogue' => true
            ));
        }

        return new Response("This is not ajax.", 400);
    }

    /**
     * [ADMIN] Gestion des items pour une boquette.
     */
    public function gestionItemAction(Request $request, Boquette $boquette)
    {
        $datatable = $this->get("pjm.datatable.boquette.item");
        $datatable->setBoquetteSlug($boquette->getSlug());
        $datatable->setAdmin(true);
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:listeItem.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * [ADMIN] Modifier l'image d'un item
     *
     * @ParamConverter("boquette", options={"mapping": {"boquette": "slug"}})
     */
    public function modifierImageItemAction(Request $request, Boquette $boquette, Item $item)
    {
        $routeRetour = "pjm_app_admin_consos_".$boquette->getSlug()."_index";

        $form = $this->createForm(new ImageItemType(), $item, array(
            'method' => 'POST',
            'action' => $this->generateUrl(
                'pjm_app_admin_boquette_modifierImageItem',
                array(
                    'boquette' => $boquette->getSlug(),
                    'item' => $item->getId()
                )
            )
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($item);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    "L'image de l'item ".$item." a été modifiée."
                );

                return $this->redirect($this->generateUrl($routeRetour));
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Un problème est survenu lors de la modification de l'image de l'item ".$item.". Réessaye."
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

        return $this->render('PJMAppBundle:Admin:imageItem.html.twig', array(
            'form' => $form->createView(),
            'item' => $item,
            'routeRetour' => $routeRetour
        ));
    }

    /**
     * [ADMIN] Gère la liste des produits mis en avant pour une boquette. (ajout et liste)
     */
    public function gestionFeaturedItemAction(Request $request, Boquette $boquette)
    {
        $featuredItem = new FeaturedItem();

        $form = $this->createForm(new FeaturedItemType(), $featuredItem, array(
            'method' => 'POST',
            'action' => $this->generateUrl(
                'pjm_app_admin_gestionFeaturedItem',
                array(
                    'slug' => $boquette->getSlug()
                )
            ),
            'boquette' => $boquette
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
                    "L'item ".$featuredItem->getItem()." est maintenant mis en avant."
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Un problème est survenu lors de la mise en avant de l'item ".$featuredItem->getItem().". Réessaye."
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl("pjm_app_admin_consos_".$boquette->getSlug()."_index"));
        }

        $datatable = $this->get("pjm.datatable.admin.featuredItem");
        $datatable->setBoquetteSlug($boquette->getSlug());
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:gestionFeaturedItem.html.twig', array(
            'form'      => $form->createView(),
            'datatable' => $datatable
        ));
    }

    /**
     * [ADMIN] Action ajax de rendu de la liste des produits mis en avant
     */
    public function featuredItemResultsAction($boquette_slug)
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.admin.featuredItem"));

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:FeaturedItem');

        $datatable->addWhereBuilderCallback($repository->callbackFindByBoquetteSlug($boquette_slug));

        return $datatable->getResponse();
    }


    /**
     * [ADMIN] Action ajax d'activation ou désactivation des responsables.
     */
    public function toggleResponsablesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $listeResponsables = $request->request->get("data");

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository("PJMAppBundle:Responsable");

            foreach ($listeResponsables as $responsableChoice) {
                $responsable = $repository->find($responsableChoice["value"]);
                $responsable->toggleActive();
                $em->persist($responsable);
            }

            $em->flush();

            return new Response("Responsables toggled.");
        }

        return new Response("This is not ajax.", 400);
    }

    /**
     * Affiche la liste des responsables d'une boquette
     * @param  object Boquette $boquette
     * @return object Template
     */
    public function voirResponsablesAction(Boquette $boquette)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Responsable');
        $responsables = $repository->findByBoquette($boquette);

        $ok = 0;
        foreach($responsables as $responsable) {
            if(!isset($oldResponsabilite) || $oldResponsabilite == $responsable->getResponsabilite()) {
                $ok++;
                $oldResponsabilite = $responsable->getResponsabilite();
            }
        }

        return $this->render('PJMAppBundle:Consos:responsables.html.twig', array(
            'responsables' => $responsables,
            'uneResp' => ($ok == count($responsables))
        ));
    }

    /**
     * Gère le rechargement d'une boquette.
     */
    public function rechargementAction(Request $request, Boquette $boquette)
    {
        $em = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $transaction->setMoyenPaiement('smoney');

        $form = $this->createForm(new MontantType(), $transaction, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_consos_rechargement', array('slug' => $boquette->getSlug()))
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
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

            return $this->redirect($this->generateUrl('pjm_app_consos_'.$boquette->getSlug().'_index'));
        }

        return $this->render("PJMAppBundle::form_only.html.twig", array(
            'form' => $form->createView(),
        ));
    }
}
