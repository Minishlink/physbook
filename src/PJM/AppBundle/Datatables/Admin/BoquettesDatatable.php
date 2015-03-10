<?php

namespace PJM\AppBundle\Datatables\Admin;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class BoquettesDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class BoquettesDatatable extends AbstractDatatableView
{
    protected $twigExt;

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
            ->setOrder(array("column" => 0, "direction" => "asc"))
        ;

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_app_admin_boquettesResults'));

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('nom', 'column', array(
                'title' => 'Nom',
            ))
            ->add('slug', 'column', array(
                'title' => 'Slug',
            ))
            ->add("image.id", "column", array("visible" => false))
            ->add('image.ext', "column", array("visible" => false))
            ->add('image.alt', 'column', array(
                'title' => 'Logo',
                "width" => "100px"
            ))
            ->add('caisseSMoney', 'column', array(
                "title" => "Caisse S-Money",
            ))
            ->add(null, "action", array(
                "title" => "Actions",
                "actions" => array(
                    array(
                        "route" => "pjm_app_admin_gestionBoquettes",
                        "route_parameters" => array(
                            "boquette" => "id"
                        ),
                        "label" => "Modifier",
                        "icon" => "glyphicon glyphicon-edit",
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
        return 'PJM\AppBundle\Entity\Boquette';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'boquettes_datatable';
    }
}
