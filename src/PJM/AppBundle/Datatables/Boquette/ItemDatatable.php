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
            ->add("boquette.slug", "column", array("visible" => false))
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
            ->add(null, "action", array(
                "title" => "Actions",
                "actions" => array(
                    array(
                        "route" => "pjm_app_admin_boquette_modifierImageItem",
                        "route_parameters" => array(
                            "boquette" => "boquette.slug",
                            "item" => "id"
                        ),
                        "label" => "Modifier l'image",
                        "icon" => "glyphicon glyphicon-picture",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Modifier",
                            "class" => "btn btn-default btn-xs",
                            "role" => "button"
                        ),
                    ),
                )
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
