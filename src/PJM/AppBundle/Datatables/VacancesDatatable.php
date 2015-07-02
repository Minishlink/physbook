<?php

namespace PJM\AppBundle\Datatables;

/**
 * Class VacancesDatatable.
 */
class VacancesDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        parent::buildDatatable();

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('pjm_app_admin_boquette_brags_vacancesResults'),
        ));

        $this->columnBuilder
            ->add('dateDebut', 'datetime', array(
                'title' => 'Début',
                'date_format' => 'll',
            ))
            ->add('dateFin', 'datetime', array(
                'title' => 'Fin',
                'date_format' => 'll',
            ))
            ->add('fait', 'boolean', array(
                'title' => 'Fait',
                'visible' => false,
                'true_icon' => 'glyphicon glyphicon-ok',
                'false_icon' => 'glyphicon glyphicon-remove',
            ))
            ->add(null, 'action', array(
                'title' => 'Actions',
                'actions' => array(
                    array(
                        'route' => 'pjm_app_admin_boquette_brags_annulerVacances',
                        'route_parameters' => array(
                            'vacances' => 'id',
                        ),
                        'label' => 'Supprimer',
                        'icon' => 'glyphicon glyphicon-trash',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'Supprimer',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button',
                        ),
                        'confirm' => true,
                        'confirm_message' => 'Es-tu sûr ?',
                        'role' => 'ROLE_ZIBRAGS',
                        'render_if' => array(
                            // FUTURE remplacer si MAJ bundle datatable
                            'fait) == false; var dummy = function(){}; dummy(',
                        ),
                    ),
                ),
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
