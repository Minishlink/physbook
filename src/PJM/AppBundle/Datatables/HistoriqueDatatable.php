<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;

/**
 * Class HistoriqueDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class HistoriqueDatatable extends AbstractDatatableView
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        $this->getFeatures()
            ->setServerSide(true)
            ->setProcessing(true);

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_app_consos_historiqueResults'));

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('date', 'column', array('title' => 'Date',))
            ->add('nombre', 'column', array('title' => 'Nombre',))
            ->add('item.libelle', 'column', array('title' => 'Item',))
            ->add('item.boquette.nom', 'column', array('title' => 'Boquette',))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Historique';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'historique_datatable';
    }
}
