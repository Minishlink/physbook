<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;

/**
 * Class HistoriqueBoquetteDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class HistoriqueBoquetteDatatable extends AbstractDatatableView
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        $this->getFeatures()->setServerSide(false);

        $this->getOptions()
            ->setOrder(array("column" => 0, "direction" => "desc"))
        ;

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('date', 'datetime', array(
                'title' => 'Date ISO',
                'format' => '',
                'visible' => false
            ))
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'format' => 'lll'
            ))
            ->add('nom', 'column', array(
                'title' => 'Nom',
            ))
            ->add('montant', 'column', array(
                'title' => 'Montant',
            ))
            ->add('infos', 'column', array(
                'title' => 'Infos',
            ))
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
        return 'historique_boquette_datatable';
    }
}
