<?php

namespace PJM\AppBundle\Datatables\Admin;

use PJM\AppBundle\Datatables\BaseDatatable;

/**
 * Class FeaturedItemDatatable.
 */
class FeaturedItemDatatable extends BaseDatatable
{
    protected $boquetteSlug;

    public function setBoquetteSlug($boquetteSlug)
    {
        $this->boquetteSlug = $boquetteSlug;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        parent::buildDatatableView();

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('pjm_app_admin_boquette_featuredItemResults', array(
                'boquette_slug' => $this->boquetteSlug,
            )),
        ));

        $this->columnBuilder
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'll',
            ))
            ->add('item.libelle', 'column', array('title' => 'Item'))
            ->add('active', 'boolean', array(
                'title' => 'Actif',
                'true_icon' => 'glyphicon glyphicon-ok',
                'false_icon' => 'glyphicon glyphicon-remove',
                'true_label' => 'Oui',
                'false_label' => 'Non',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\FeaturedItem';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'featuredItem_datatable';
    }
}
