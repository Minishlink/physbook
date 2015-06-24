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
    public function buildDatatableView()
    {
        $this->ajax->setOptions(array('url' =>
            $this->router->generate('pjm_app_push_subscriptionResults')
        ));

        $this->columnBuilder
            ->add('lastSubscribed', 'datetime', array(
                'title' => 'Dernier accès',
                'date_format' => 'lll',
            ))
            ->add('browserUA', 'column', array(
                'title' => 'Type de navigateur',
            ))
            ->add(null, 'multiselect', array(
                'action' => array(
                    'route' => 'pjm_app_push_deleteSubscription',
                    'label' => 'Supprimer',
                    'icon' => 'glyphicon glyphicon-remove',
                ),
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
