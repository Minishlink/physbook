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
            ->add('dateDebut', 'column', array('title' => 'Début',))
            ->add('dateFin', 'column', array('title' => 'Fin',))
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
