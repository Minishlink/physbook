<?php

namespace PJM\AppBundle\Datatables\Boquette;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class ItemDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class ItemDatatable extends AbstractDatatableView
{
    protected $boquetteSlug;
    protected $twigExt;

    public function setBoquetteSlug($boquetteSlug)
    {
        $this->boquetteSlug = $boquetteSlug;
    }

    public function setTwigExt(IntranetExtension $twigExt)
    {
        $this->twigExt = $twigExt;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        $this->getFeatures()
            ->setServerSide(true)
            ->setProcessing(true)
        ;

        $this->getOptions()
            ->setOrder(array("column" => 3, "direction" => "desc"))
        ;

        $this->getAjax()->setUrl(
            $this->getRouter()->generate('pjm_app_boquette_itemResults', array(
                'boquette_slug' => $this->boquetteSlug
            ))
        );

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add("image.id", "column", array("visible" => false))
            ->add('image.ext', "column", array("visible" => false))
            ->add('image.alt', 'column', array(
                'title' => 'Image',
            ))
            ->add('libelle', 'column', array(
                'title' => 'Nom'
            ))
            ->add('prix', 'column', array(
                'title' => 'Prix',
            ))
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'format' => 'll'
            ))
            ->add("valid", "boolean", array(
                "title" => "Actif",
                "true_icon" => "glyphicon glyphicon-ok",
                "false_icon" => "glyphicon glyphicon-remove",
                "true_label" => "Oui",
                "false_label" => "Non"
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = $this->twigExt;
        $formatter = function($line) use($ext) {
            $line["prix"] = $ext->prixFilter($line["prix"]);
            $line["image"]["alt"] = !empty($line["image"]["id"]) ?
                $ext->imageFunction($line["image"]["id"], $line["image"]["ext"], $line["image"]["alt"]) :
                "Pas d'image";

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Item';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pjm_app_boquette_item_datatable';
    }
}
