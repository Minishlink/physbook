<?php

namespace PJM\AppBundle\Datatables;

use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class HistoriqueAdminDatatable.
 */
class HistoriqueAdminDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        parent::buildDatatableView();

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('pjm_app_admin_boquette_brags_bucquagesResults'),
        ));

        $this->columnBuilder
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'll',
            ))
            ->add('user.username', 'column', array('title' => 'PG'))
            ->add('item.libelle', 'column', array('title' => 'Item'))
            ->add('nombre', 'column', array('title' => 'Nombre'))
            ->add('item.prix', 'column', array('title' => 'P.U.'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = new IntranetExtension();
        $formatter = function ($line) use ($ext) {
            $line['item']['prix'] = $ext->prixFilter($line['item']['prix']);
            $line['nombre'] = $ext->nombreFilter($line['nombre']);

            return $line;
        };

        return $formatter;
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
        return 'historique_admin_datatable';
    }
}
