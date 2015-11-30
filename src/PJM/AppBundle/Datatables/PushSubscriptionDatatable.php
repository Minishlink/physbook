<?php

namespace PJM\AppBundle\Datatables;

/**
 * Class PushSubscriptionDatatable.
 */
class PushSubscriptionDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        parent::buildDatatable();

        $this->options->setOption('order', [[1, 'desc']]);

        $this->ajax->setOptions(array('url' => $this->router->generate('pjm_app_api_pushsubscription_results')));

        $this->columnBuilder
            ->add(null, 'multiselect', array(
                'actions' => array(
                    array(
                        'route' => 'pjm_app_api_pushsubscription_bulkdelete',
                        'label' => 'Supprimer',
                        'icon' => 'glyphicon glyphicon-remove',
                        'attributes' => array(
                            'class' => 'btn btn-primary btn-xs',
                        ),
                    ),
                ),
                'width' => '20px',
            ))
            ->add('lastSubscribed', 'datetime', array(
                'title' => 'Dernier accès',
                'date_format' => 'lll',
            ))
            ->add('browserUA', 'column', array(
                'title' => 'Type de navigateur',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\PushSubscription';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pushsubscription_datatable';
    }
}
