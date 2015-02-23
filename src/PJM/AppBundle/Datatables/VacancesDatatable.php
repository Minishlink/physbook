<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;

/**
 * Class VacancesDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class VacancesDatatable extends AbstractDatatableView
{
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
            ->setOrder(array("column" => 0, "direction" => "desc"))
        ;

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_app_admin_consos_brags_vacancesResults'));

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('dateDebut', 'datetime', array(
                'title' => 'Date ISO',
                'format' => '',
                'visible' => false
            ))
            ->add('dateDebut', 'datetime', array(
                'title' => 'Début',
                'format' => 'll'
            ))
            ->add('dateFin', 'datetime', array(
                'title' => 'Fin',
                'format' => 'll'
            ))
            ->add("fait", "boolean", array(
                "title" => "Fait",
                "visible" => false,
                "true_icon" => "glyphicon glyphicon-ok",
                "false_icon" => "glyphicon glyphicon-remove"
            ))
            ->add(null, "action", array(
                "title" => "Actions",
                "actions" => array(
                    array(
                        "route" => "pjm_app_admin_consos_brags_annulerVacances",
                        "route_parameters" => array(
                            "vacances" => "id"
                        ),
                        "icon" => "glyphicon glyphicon-trash",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Supprimer",
                            "class" => "btn btn-primary btn-xs",
                            "role" => "button"
                        ),
                        "confirm" => true,
                        "confirm_message" => "Es-tu sûr ?",
                        "role" => "ROLE_ZIBRAGS",
                        "renderif" => array(
                            // #FUTURE remplacer si MAJ bundle datatable
                            "fait) == false; var dummy = function(){}; dummy("
                        )
                    ),
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Vacances';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'vacances_datatable';
    }
}
