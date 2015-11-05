<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Form\Type\Consos\MontantType;

class BoquetteController extends Controller
{
    private function getTwigTemplatePath($boquetteSlug)
    {
        switch ($boquetteSlug) {
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

    /**
     * Page par défaut des boquettes.
     *
     * @param object   Boquette $boquette
     */
    public function defaultAction(Boquette $boquette)
    {
        return $this->render('PJMAppBundle:Boquette:default.html.twig', array(
            'boquette' => $boquette,
        ));
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
        $datatable->buildDatatable();
        $datatable->setData($serializer->serialize($historique, 'json'));

        $layout = $this->getTwigTemplatePath($boquette->getSlug()).'layout.html.twig';
        $routeRetour = 'pjm_app_boquette_'.$boquette->getSlug().'_index';

        return $this->render('PJMAppBundle::datatable.html.twig', array(
            'titre' => 'Historique',
            'layout' => $layout,
            'routeRetour' => $routeRetour,
            'datatable' => $datatable,
        ));
    }

    /**
     * Liste des items pour une boquette.
     */
    public function listeItemAction(Request $request, Boquette $boquette)
    {
        $datatable = $this->get('pjm.datatable.boquette.item');
        $datatable->setBoquetteSlug($boquette->getSlug());
        $datatable->setAdmin(false);
        $datatable->buildDatatable();

        $layout = $this->getTwigTemplatePath($boquette->getSlug()).'layout.html.twig';
        $routeRetour = 'pjm_app_boquette_'.$boquette->getSlug().'_index';

        return $this->render('PJMAppBundle::datatable.html.twig', array(
            'titre' => 'Catalogue',
            'layout' => $layout,
            'routeRetour' => $routeRetour,
            'datatable' => $datatable,
        ));
    }

    /**
     * Action ajax de rendu de la liste des items pour DataTables (on n'affiche pas les évènements).
     */
    public function itemResultsAction($boquette_slug)
    {
        $datatable = $this->get('pjm.datatable.boquette.item');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Item');
        $query->addWhereAll($repository->callbackFindByBoquetteSlug($boquette_slug));

        return $query->getResponse();
    }

    public function getItemsAction(Request $request, $boquette_slug, $offset = 0)
    {
        if ($request->isXmlHttpRequest()) {
            $listeItems = $this->get('pjm.services.boquette')->getItems($boquette_slug, true, null, $offset);

            return $this->render('PJMAppBundle:Consos/Cvis:produits.html.twig', array(
                'listeProduits' => $listeItems,
                'ajoutCatalogue' => true,
            ));
        }

        return $this->redirect($this->generateUrl('pjm_app_homepage'));
    }

    /**
     * Affiche la liste des responsables d'une boquette.
     *
     * @param object Boquette $boquette
     *
     * @return object Template
     */
    public function voirResponsablesAction(Boquette $boquette)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Responsable');
        $responsables = $repository->findByBoquette($boquette);

        $ok = 0;
        foreach ($responsables as $responsable) {
            if (!isset($oldResponsabilite) || $oldResponsabilite == $responsable->getResponsabilite()) {
                ++$ok;
                $oldResponsabilite = $responsable->getResponsabilite();
            }
        }

        return $this->render('PJMAppBundle:Consos:responsables.html.twig', array(
            'responsables' => $responsables,
            'uneResp' => ($ok == count($responsables)),
        ));
    }

    /**
     * Affiche l'historique des responsables d'une boquette.
     *
     * @param object Boquette $boquette
     *
     * @return object Template
     */
    public function voirHistoriqueResponsablesAction(Boquette $boquette)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Responsable');
        $responsables = $repository->findByBoquette($boquette, null);

        $proms = array();
        foreach ($responsables as $responsable) {
            if (!in_array($responsable->getUser()->getProms(), $proms)) {
                $proms[] = $responsable->getUser()->getProms();
            }
        }

        asort($proms);

        return $this->render('PJMAppBundle:Boquette:historiqueResponsables.html.twig', array(
            'responsables' => $responsables,
            'listeProms' => $proms,
        ));
    }

    /**
     * Gère le rechargement d'une boquette.
     */
    public function rechargementAction(Request $request, Boquette $boquette, $montant = null)
    {
        $em = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $transaction->setMoyenPaiement('smoney');

        if ($montant !== null) {
            $transaction->setMontant($montant);
        }

        $form = $this->createForm(new MontantType(), $transaction, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_boquette_rechargement', array('slug' => $boquette->getSlug())),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $compte = $em->getRepository('PJMAppBundle:Compte')->findOneByUserAndBoquetteSlug($this->getUser(), $boquette->getSlug());
                $transaction->setCompte($compte);
                $transaction->setInfos($boquette->getCaisseSMoney());

                $resInitPayment = $this->get('pjm.services.payments.lydia')->requestRemote($transaction, array(
                    'confirm_url' => $this->generateUrl('pjm_app_consos_rechargement_confirm', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                    'cancel_url' => $this->generateUrl('pjm_app_consos_rechargement_cancel', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                    'expire_url' => $this->generateUrl('pjm_app_consos_rechargement_expire', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                    'browser_success_url' => $this->generateUrl('pjm_app_notifications_index', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                    'browser_fail_url' => $this->generateUrl('pjm_app_notifications_index', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                ));

                if (!$resInitPayment['success']) {
                    // erreur
                    $this->get('pjm.services.notification')->sendFlash(
                        'danger',
                        'Il y a eu une erreur lors de la communication avec Lydia. Erreur '.$resInitPayment['errorCode'].' : '.$resInitPayment['errorMessage']
                    );
                }

                // succès, on redirige vers l'URL de paiement
                return $this->redirect($resInitPayment['url']);
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de l\'envoi du formulaire de rechargement. Réessaye.'
                );

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_boquette_'.$boquette->getSlug().'_index'));
        }

        return $this->render('PJMAppBundle::form_only.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
