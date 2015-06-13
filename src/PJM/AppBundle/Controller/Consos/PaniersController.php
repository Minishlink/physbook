<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\Historique;
use PJM\AppBundle\Form\Consos\PanierType;

class PaniersController extends BoquetteController
{
    public function __construct()
    {
        $this->slug = 'paniers';
        $this->itemSlug = 'panier';
    }

    public function indexAction(Request $request)
    {
        $panier = $this->getCurrentPanier();
        $commande = $this->getCommande($panier);

        return $this->render('PJMAppBundle:Consos:Paniers/index.html.twig', array(
            'boquetteSlug' => $this->slug,
            'panier' => $panier,
            'dejaCommande' => isset($commande),
            'solde' => $this->getSolde(),
        ));
    }

    private function getCommande(Item $panier)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Historique');
        $commande = $repository->findOneBy(array(
            'user' => $this->getUser(),
            'item' => $panier,
            'valid' => true,
        ));

        return $commande;
    }

    public function commanderAction(Request $request)
    {
        // on va chercher le dernier panier
        $panier = $this->getCurrentPanier();

        // si le panier est bien actif
        if (isset($panier) && $panier->getValid()) {
            // on vérifie si l'utilisateur n'a pas déjà commandé un panier
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('PJMAppBundle:Historique');
            $commandes = $repository->findByUserAndItem($this->getUser(), $panier);
            if (empty($commandes)) {
                // on vérifie que l'utilisateur ait assez d'argent
                $repository = $em->getRepository('PJMAppBundle:Compte');
                if (null !== $repository->findOneByUserAndBoquetteAndMinSolde($this->getUser(), $panier->getBoquette(), $panier->getPrix())) {
                    // on enregistre dans l'historique
                    $achat = new Historique();
                    $achat->setUser($this->getUser());
                    $achat->setItem($panier);
                    $achat->setValid(true);

                    $em->persist($achat);
                    $em->flush();

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        'Le panier a été commandé. Tu pourras le récupérer chez le ZiPaniers ou dans le local du C\'vis. N\'oublie pas ce jour-là d\'indiquer que tu l\'as récupéré en signant la feuille de reçu.'
                    );
                } else {
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        'Tu n\'as pas assez d\'argent sur ton compte.'
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Tu as déjà commandé ce panier.'
                );
            }
        } else {
            $request->getSession()->getFlashBag()->add(
                'danger',
                "Désolé, c'est trop tard pour commander ce panier."
            );
        }

        return $this->redirect($this->generateUrl('pjm_app_boquette_paniers_index'));
    }

    public function annulerAction(Request $request)
    {
        // on va chercher le dernier panier
        $panier = $this->getCurrentPanier();

        if (isset($panier)) {
            $commande = $this->getCommande($panier);
            // si on a commandé le panier et que le panier est actif
            if (isset($commande) && $panier->getValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($commande);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'Ta commande de panier a été annulée et tu as été remboursé.'
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Tu n'as pas commandé ce panier ou ce panier n'est plus annulable automatiquement car les commandes sont déjà prises au prestataire. Contacte le ZiPaniers."
                );
            }
        } else {
            $request->getSession()->getFlashBag()->add(
                'danger',
                "Il n'y a pas de panier disponible actuellement."
            );
        }

        return $this->redirect($this->generateUrl('pjm_app_boquette_paniers_index'));
    }

    private function getCurrentPanier()
    {
        return $this->getLastItem($this->itemSlug, 'any');
    }

    /*
    * ADMIN
    */
    public function adminAction()
    {
        // TODO faire reloguer l'utilisateur sauf si redirection depuis l'admin

        return $this->render('PJMAppBundle:Admin:Consos/Paniers/index.html.twig', array(
            'boquetteSlug' => $this->slug
        ));
    }

    // ajout et liste paniers
    public function gestionPaniersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $panier = new Item();
        $panier->setLibelle('Panier de fruits et légumes');
        $panier->setBoquette($this->getBoquette());
        $panier->setSlug($this->itemSlug);

        $form = $this->createForm(new PanierType(), $panier, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_admin_boquette_paniers_listePaniers'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on enregistre le nouveau panier et on désactive l'ancien
                $ancienPanier = $this->getCurrentPanier();
                if (isset($ancienPanier)) {
                    $ancienPanier->setValid(false);
                    $em->persist($ancienPanier);
                }

                $panier->setLibelle('Panier de fruits et légumes ('.$panier->getDate()->format('d/m').")");
                $em->persist($panier);

                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'Le panier a bien été ajouté.'
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de l\'ajout du panier. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_boquette_paniers_index'));
        }

        $datatable = $this->get("pjm.datatable.admin.paniers.liste");
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:Consos/Paniers/listePaniers.html.twig', array(
            'form' => $form->createView(),
            'datatable' => $datatable
        ));
    }

    // action ajax de rendu de la liste des paniers
    public function paniersResultsAdminAction()
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.admin.paniers.liste"));
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Item');
        $datatable->addWhereBuilderCallback($repository->callbackFindBySlug($this->itemSlug));

        return $datatable->getResponse();
    }

    public function voirCommandesAction(Request $request, Item $panier, $download = false)
    {
        if ($panier->getSlug() == $this->itemSlug) {
            // voir qui a pris les paniers
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('PJMAppBundle:Historique');
            $commandes = $repository->findByItem($panier, null, true);

            if (empty($commandes)) {
                $request->getSession()->getFlashBag()->add(
                    'warning',
                    "Il n'y a pas encore eu de commandes pour ce panier."
                );

                return $this->redirect($this->generateUrl('pjm_app_admin_boquette_paniers_index'));
            }

            // on transforme tout ça en tableau lisible
            $tableau = array();
            foreach ($commandes as $commande) {
                $row['bucque'] = $commande->getUser()->getBucque();
                $row['fams'] = $commande->getUser()->getFams();
                $row['tabagns'] = $commande->getUser()->getTabagns();
                $row['proms'] = $commande->getUser()->getProms();
                $tableau[] = $row;
            }

            /*
             * Si on veut télécharger le fichier Excel
             * alors on arrête les commandes de ce panier
             * et on fait télécharger le fichier
             */
            if ($download) {
                // on arrête les commandes
                if ($panier->getValid()) {
                    $panier->setValid(false);
                    $em->persist($panier);
                    $em->flush();
                }

                // on appelle le service PHPExcel
                $excel = $this->get('pjm.services.excel');
                $phpExcelObject = $excel->create($panier->getLibelle());

                $entetes = array(
                    'Bucque',
                    "Fam's",
                    "Tbk",
                    "Prom's",
                    "Signature"
                );

                $sheet = $excel->setData($entetes, $tableau, 'A', '3', 'Commandes');

                // on met en forme
                $nbRows = count($tableau);
                $rangeTab = $excel->getRangeString();
                $sheet
                    ->setCellValue('A1', "Total")
                    ->setCellValue('B1', count($tableau))
                    ->setCellValue('C1', "paniers")
                    ->setCellValue('D1', "soit")
                    ->setCellValue('E1', count($tableau)*$panier->getPrix()/100)
                ;

                $boldStyle = array(
                    'font' => array(
                        'bold' => true
                    ),
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => '00000000'),
                        ),
                    ),
                );

                $italicStyle = array(
                    'font' => array(
                        'italic' => true
                    )
                );

                $borduresIntStyle = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('rgb' => 'b0b0b0'),
                        ),
                    ),
                );

                $borduresStyle = array(
                    'borders' => array(
                        'outline' => array(
                            'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,
                            'color' => array('argb' => '00000000'),
                        ),
                    ),
                );

                $sheet->getStyle('E1')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
                $sheet->getStyle('A1')->applyFromArray($italicStyle);
                $sheet->getStyle($rangeTab)->applyFromArray($borduresIntStyle);
                $sheet->getStyle('A3:E3')->applyFromArray($boldStyle);
                $sheet->getStyle($rangeTab)->applyFromArray($borduresStyle);
                $sheet->getStyle($rangeTab)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $sheet->getColumnDimension('A')->setWidth(13);
                $sheet->getColumnDimension('E')->setWidth(15);
                for ($r = 0; $r < $nbRows; $r++) {
                    $sheet->getRowDimension(4+$r)->setRowHeight(25);
                }

                // on télécharge
                return $excel->download(
                    'commandes-'.$panier->getDate()->format('d-m-Y')
                );
            }

            // sinon on veut juste voir l'avancement
            return $this->render('PJMAppBundle:App:table.html.twig', array(
                'table' => $tableau,
            ));
        }

        return new Response("Ce n'est pas un panier.", 404);
    }
}
