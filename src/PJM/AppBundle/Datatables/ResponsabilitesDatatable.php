<?php

namespace PJM\AppBundle\Datatables;

/**
 * Class ResponsabilitesDatatable.
 */
class ResponsabilitesDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        parent::buildDatatable();

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('pjm_app_admin_responsabilitesResults'),
        ));

        $this->columnBuilder
            ->add('libelle', 'column', array(
                'title' => 'Libellé',
            ))
            ->add('boquette.nom', 'column', array(
                'title' => 'Boquette',
            ))
            ->add('niveau', 'column', array(
                'title' => 'Niveau',
            ))
            ->add('role', 'column', array(
                'title' => 'Rôle',
            ))
            ->add('active', 'boolean', array(
                'title' => 'Active',
                'true_icon' => 'glyphicon glyphicon-ok',
                'false_icon' => 'glyphicon glyphicon-remove',
                'true_label' => 'Oui',
                'false_label' => 'Non',
            ))
            ->add(null, 'action', array(
                'title' => 'Actions',
                'actions' => array(
                    array(
                        'route' => 'pjm_app_admin_responsabilites',
                        'route_parameters' => array(
                            'responsabilite' => 'id',
                        ),
                        'label' => 'Modifier',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'Modifier',
                            'class' => 'btn btn-default btn-xs',
                            'role' => 'button',
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
        return 'PJM\AppBundle\Entity\Responsabilite';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'responsabilites_datatable';
    }
}
