<?php

namespace PJM\AppBundle\Datatables;

/**
 * Class HistoriqueBoquetteDatatable.
 */
class HistoriqueBoquetteDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable($locale = null)
    {
        parent::buildDatatable($locale);

        $this->features->set(array(
            'server_side' => false,
        ));

        $this->columnBuilder
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'lll',
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
