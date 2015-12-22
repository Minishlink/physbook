<?php

namespace PJM\AppBundle\Datatables;

/**
 * Class AchatsDatatable.
 */
class AchatsDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable($locale = null)
    {
        parent::buildDatatable($locale);

        $this->ajax->set(array(
            'url' => $this->ajaxUrl ? $this->ajaxUrl : '',
        ));

        $this->columnBuilder
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'lll',
            ))
        ;

        if (!$this->admin) {
            $this->columnBuilder
                ->add('item.boquette.nom', 'column', array('title' => 'Boquette'))
            ;
        } else {
            $this->columnBuilder
                ->add('user.username', 'column', array('title' => 'PG'))
            ;
        }

        $this->columnBuilder
            ->add('item.libelle', 'column', array('title' => 'Item'))
            ->add('nombre', 'column', array('title' => 'Nombre'))
            ->add('item.prix', 'column', array('title' => 'Prix'))
            ->add('valid', 'boolean', array(
                'title' => 'Effectué',
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
    public function getLineFormatter()
    {
        $formatter = function ($line) {
            $line['nombre'] = $this->intranetExt->nombreFilter($line['nombre']);
            $line['item']['prix'] = $this->intranetExt->prixFilter($line['nombre'] * $line['item']['prix']);

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
        return 'achats_datatable';
    }
}
