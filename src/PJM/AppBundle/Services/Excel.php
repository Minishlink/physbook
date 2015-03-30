<?php

namespace PJM\AppBundle\Services;

use Symfony\Bridge\Monolog\Logger;

class Excel
{
    protected $phpExcel;
    protected $logger;
    protected $phpExcelObject;
    protected $range;

    public function __construct(\Liuggio\ExcelBundle\Factory $phpExcel, Logger $logger)
    {
        $this->phpExcel = $phpExcel;
        $this->logger = $logger;
    }

    public function setPhpExcelObject(\PHPExcel $phpExcelObject)
    {
        $this->phpExcelObject = $phpExcelObject;

        return $this->phpExcelObject;
    }

    public function getPhpExcelObject()
    {
        return $this->phpExcelObject;
    }

    public function getRange()
    {
        return $this->range;
    }

    public function getRangeString()
    {
        return $this->range[0][0].$this->range[0][1].":".$this->range[1][0].$this->range[1][1];
    }

    /**
     * Retourne le phpExcelObject avec lequel travailler
     * @param string $titre Titre du document
     */
    public function create($titre)
    {
        $this->phpExcelObject = $this->phpExcel->createPHPExcelObject();

        // on définit le nom du fichier
        $this->phpExcelObject->getProperties()->setCreator("Phy'sbook")
            ->setLastModifiedBy("Phy'sbook")
            ->setTitle($titre)
        ;

        $sheet = $this->phpExcelObject->setActiveSheetIndex(0);

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

        return $this->phpExcelObject;
    }

    public function setData($entetes, $tableau, $firstCol = null, $firstRow = null, $titre = null)
    {
        $sheet = $this->phpExcelObject->setActiveSheetIndex(0);

        if ($firstCol === null) {
            $firstCol = 'A';
        }

        if ($firstRow === null) {
            $firstRow = '1';
        }

        $nbCols = count($entetes);
        $lastCol = $firstCol;
        for ($i = 1; $i <= $nbCols; $i++) {
            $sheet->setCellValue($lastCol.$firstRow, $entetes[$i-1]);

            if ($i != $nbCols) {
                $lastCol++;
            }
        }

        $nbRows = count($tableau);
        $lastRow = $firstRow;
        for ($i = 0; $i < $nbRows; $i++) {
            $lastRow++;
        }

        $this->range = array(
            array($firstCol, $firstRow),
            array($lastCol, $lastRow)
        );

        $range = $this->getRangeString();

        $sheet
            ->fromArray($tableau, NULL, $firstCol.(++$firstRow))
            ->setAutoFilter($range)
        ;

        if ($titre !== null) {
            $sheet->setTitle($titre);
        }

        return $sheet;
    }

    /**
     * Télécharge le fichier excel
     * @param  string $filename Le nom du fichier (sans l'extension)
     * @return object La réponse à retourner au controlleur.
     */
    public function download($filename)
    {
        // on met le curseur au début du fichier
        $this->phpExcelObject->setActiveSheetIndex(0);

        // on fait télécharger le fichier
        $writer = $this->phpExcel->createWriter($this->phpExcelObject, 'Excel2007');
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

        $this->phpExcelObject = $this->phpExcel->createPHPExcelObject($url);
        $sheetData = $this->phpExcelObject->getActiveSheet()->toArray(null,true,true,true);

        return $sheetData;
    }
}
