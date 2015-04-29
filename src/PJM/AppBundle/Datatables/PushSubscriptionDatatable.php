<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;

/**
 * Class PushSubscriptionDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class PushSubscriptionDatatable extends AbstractDatatableView
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

        $this->getAjax()->setUrl(
            $this->getRouter()->generate('pjm_app_push_subscriptionResults')
        );

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getMultiselect()
            ->setEnabled(true)
            ->setPosition("last")
            ->addAction("Supprimer", "pjm_app_push_deleteSubscription")
            ->setWidth("20px")
        ;

        $this->getColumnBuilder()
            ->add("lastSubscribed", "datetime", array(
                "title" => "Dernier accès",
                "format" => "lll"
            ))
            ->add("browserUA", "column", array(
                "title" => "Type de navigateur",
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
