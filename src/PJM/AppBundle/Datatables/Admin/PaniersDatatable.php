<?php

namespace PJM\AppBundle\Datatables\Admin;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class PaniersDatatable
 *
 * @package PJM\AppBundle\Datatables\Admin
 */
class PaniersDatatable extends AbstractDatatableView
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        $this->getFeatures()
            ->setServerSide(true)
            ->setProcessing(true);

        $this->getOptions()
            ->setOrder(array("column" => 0, "direction" => "desc"))
        ;

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_app_admin_consos_paniers_paniersResults'));

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'format' => 'll'
            ))
            ->add('infos', 'column', array('title' => 'Infos',))
            ->add('prix', 'column', array('title' => 'Prix',))
            ->add(null, "action", array(
                "title" => "Actions",
                "actions" => array(
                    array(
                        "route" => "pjm_app_admin_consos_paniers_voirCommandes",
                        "route_parameters" => array(
                            "panier" => "id"
                        ),
                        "label" => "Commandes",
                        "icon" => "glyphicon glyphicon-save",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Télécharger les commandes",
                            "class" => "btn btn-default btn-xs",
                            "role" => "button"
                        ),
                    )
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = new IntranetExtension();
        $formatter = function($line) use($ext) {
            $line["prix"] = $ext->prixFilter($line["prix"]);
            $line["infos"] = json_decode($line["infos"]);
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
        return 'historique_datatable';
    }
}
