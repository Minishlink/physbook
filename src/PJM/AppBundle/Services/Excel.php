<?php

namespace PJM\AppBundle\Services;

use Symfony\Bridge\Monolog\Logger;

class Excel
{
    protected $phpExcel;
    protected $logger;

    public function __construct(\Liuggio\ExcelBundle\Factory $phpExcel, Logger $logger)
    {
        $this->phpExcel = $phpExcel;
        $this->logger = $logger;
    }

    /**
     * Retourne le phpExcelObject avec lequel travailler
     * @param string $titre Titre du document
     */
    public function create($titre)
    {
        $phpExcelObject = $this->phpExcel->createPHPExcelObject();

        // on définit le nom du fichier
        $phpExcelObject->getProperties()->setCreator("Phy'sbook")
            ->setLastModifiedBy("Phy'sbook")
            ->setTitle($titre)
        ;

        $sheet = $phpExcelObject->setActiveSheetIndex(0);

        // on charge le logo
        $logo = new \PHPExcel_Worksheet_HeaderFooterDrawing();
        $logo->setName("Phy'sbook logo");
        /*$urlLogo = parse_url($this->get('templating.helper.assets')->getUrl('/images/general/logo+banniere.png'), PHP_URL_PATH);
        $basePath = $_SERVER['DOCUMENT_ROOT'];
        $basePath .= "/web";
        $logo->setPath($basePath.$urlLogo);*/
        $logo->setPath('images/general/logo+banniere.png');
        $logo->setHeight(40);
        $sheet->getHeaderFooter()->addImage($logo, \PHPExcel_Worksheet_HeaderFooter::IMAGE_HEADER_LEFT);

        // on met le titre et le logo
        $sheet->getHeaderFooter()->setOddHeader('&L&G&C&20 '.$titre);

        // on met un petit message d'horodatage
        $sheet->getHeaderFooter()->setOddFooter("&LAutogénéré le &D à &T.&Rphysbook.fr");

        return $phpExcelObject;
    }

    /**
     * Télécharge le fichier excel
     * @param  object \PHPExcel $phpExcelObject Le phpExcelObject sur lequel on a travaillé
     * @param  string $filename Le nom du fichier (sans l'extension)
     * @return object La réponse à retourner au controlleur.
     */
    public function download(\PHPExcel $phpExcelObject, $filename)
    {
        // on met le curseur au début du fichier
        $phpExcelObject->setActiveSheetIndex(0);

        // on fait télécharger le fichier
        $writer = $this->phpExcel->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->phpExcel->createStreamedResponse($writer);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename.'.xlsx');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }

    /**
     * Lit un fichier Excel
     * @param string $url L'URL du fichier à lire
     * @return Le tableau associé à l'Excel
     */
    public function parse($url)
    {
        if (!file_exists($url)) {
            $this->logger->warn("Le fichier '".$url."' n'existe pas !");
            return;
        }

        $phpExcelObject = $this->phpExcel->createPHPExcelObject($url);
        $sheetData = $phpExcelObject->getActiveSheet()->toArray(null,true,true,true);

        return $sheetData;
    }
}
