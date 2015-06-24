<?php

namespace PJM\AppBundle\Datatables;

use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class PrixDatatable.
 */
class PrixDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        $this->ajax->setOptions(array(
            'url' => $this->router->generate('pjm_app_admin_boquette_brags_prixResults'),
        ));

        $this->columnBuilder
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'll',
            ))
            ->add('prix', 'column', array(
                'title' => 'Prix',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = new IntranetExtension();
        $formatter = function ($line) use ($ext) {
            $line['prix'] = $ext->prixFilter($line['prix']);

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Item';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'prix_datatable';
    }
}
