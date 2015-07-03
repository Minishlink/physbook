<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Form\Type\Consos\PanierType;

class PaniersAdminController extends Controller
{
    private $slug;
    private $itemSlug;

    public function __construct()
    {
        $this->slug = 'paniers';
        $this->itemSlug = 'panier';
    }

    public function indexAction()
    {
        return $this->render('PJMAppBundle:Admin:Consos/Paniers/index.html.twig', array(
            'boquetteSlug' => $this->slug,
        ));
    }

    // ajout et liste paniers
    public function gestionPaniersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $paniersService = $this->get('pjm.services.boquette.paniers');

        $panier = new Item();
        $panier->setLibelle('Panier de fruits et légumes');
        $panier->setBoquette($paniersService->getBoquette());
        $panier->setSlug($this->itemSlug);

        $form = $this->createForm(new PanierType(), $panier, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_admin_boquette_paniers_listePaniers'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on enregistre le nouveau panier et on désactive l'ancien
                $ancienPanier = $paniersService->getCurrentPanier();
                if (isset($ancienPanier)) {
                    $ancienPanier->setValid(false);
                    $em->persist($ancienPanier);
                }

                $panier->setLibelle('Panier de fruits et légumes ('.$panier->getDate()->format('d/m').')');
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

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_admin_boquette_paniers_index'));
        }

        $datatable = $this->get('pjm.datatable.admin.paniers.liste');
        $datatable->buildDatatable();

        return $this->render('PJMAppBundle:Admin:Consos/Paniers/listePaniers.html.twig', array(
            'form' => $form->createView(),
            'datatable' => $datatable,
        ));
    }

    // action ajax de rendu de la liste des paniers
    public function paniersResultsAdminAction()
    {
        $datatable = $this->get('pjm.datatable.admin.paniers.liste');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Item');
        $query->addWhereResult($repository->callbackFindBySlug($this->itemSlug));

        return $query->getResponse();
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
                $excel->create($panier->getLibelle());

                $entetes = array(
                    'Bucque',
                    "Fam's",
                    'Tbk',
                    "Prom's",
                    'Signature',
                );

                $sheet = $excel->setData($entetes, $tableau, 'A', '3', 'Commandes');

                // on met en forme
                $nbRows = count($tableau);
                $rangeTab = $excel->getRangeString();
                $sheet
                    ->setCellValue('A1', 'Total')
                    ->setCellValue('B1', count($tableau))
                    ->setCellValue('C1', 'paniers')
                    ->setCellValue('D1', 'soit')
                    ->setCellValue('E1', count($tableau) * $panier->getPrix() / 100)
                ;

                $boldStyle = array(
                    'font' => array(
                        'bold' => true,
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
                        'italic' => true,
                    ),
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
                for ($r = 0; $r < $nbRows; ++$r) {
                    $sheet->getRowDimension(4 + $r)->setRowHeight(25);
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
