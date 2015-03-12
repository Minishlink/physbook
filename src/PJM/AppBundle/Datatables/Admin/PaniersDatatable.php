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

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_app_admin_boquette_paniers_paniersResults'));

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('date', 'datetime', array(
                'title' => 'Date ISO',
                'format' => '',
                'visible' => false
            ))
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'format' => 'll'
            ))
            ->add('infos', 'column', array('title' => 'Infos',))
            ->add('prix', 'column', array('title' => 'Prix',))
            ->add("valid", "boolean", array(
                "title" => "Actif",
                "true_icon" => "glyphicon glyphicon-ok",
                "false_icon" => "glyphicon glyphicon-remove",
                "true_label" => "Oui",
                "false_label" => "Non"
            ))
            ->add(null, "action", array(
                "title" => "Commandes",
                "actions" => array(
                    array(
                        "route" => "pjm_app_admin_boquette_paniers_voirCommandes",
                        "route_parameters" => array(
                            "panier" => "id",
                        ),
                        "label" => "Voir l'état",
                        "icon" => "glyphicon glyphicon-eye-open",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Voir l'état des commandes",
                            "class" => "btn btn-default btn-xs",
                            "role" => "button"
                        ),
                    ),
                    array(
                        "route" => "pjm_app_admin_boquette_paniers_telechargerCommandes",
                        "route_parameters" => array(
                            "panier" => "id",
                        ),
                        "label" => "Stopper et télécharger",
                        "icon" => "glyphicon glyphicon-save",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Télécharger et arrêter les commandes (.xlsx)",
                            "class" => "btn btn-default btn-xs",
                            "role" => "button",
                        ),
                        "confirm" => true,
                        "confirm_message" => "Attention, cela va stopper les prises de commandes pour ce panier si elles ne le sont pas encore. Es-tu sûr ?",
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
        $ext = new IntranetExtension();
        $formatter = function($line) use($ext) {
            $line["prix"] = $ext->prixFilter($line["prix"]);
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
