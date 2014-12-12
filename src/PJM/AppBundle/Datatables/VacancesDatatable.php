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
            ->setProcessing(true);

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_app_consos_brags_admin_vacancesResults'));

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('dateDebut', 'datetime', array(
                'title' => 'Début',
                'format' => 'll'
            ))
            ->add('dateFin', 'datetime', array(
                'title' => 'Fin',
                'format' => 'll'
            ))
            ->add(null, "action", array(
                "title" => "Actions",
                "start" => '<div class="wrapper_example_class">',
                "end" => '</div>',
                "actions" => array(
                    array(
                        "route" => "pjm_app_consos_brags_admin_annulerVacances",
                        "route_parameters" => array(
                            "vacances" => "id"
                        ),
                        "icon" => "glyphicon glyphicon-remove",
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
