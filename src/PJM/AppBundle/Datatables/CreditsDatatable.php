<?php

namespace PJM\AppBundle\Datatables;

/**
 * Class CreditsDatatable.
 */
class CreditsDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        parent::buildDatatable();

        $this->ajax->setOptions(array(
            'url' => $this->ajaxUrl ? $this->ajaxUrl : '',
        ));

        $this->columnBuilder
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'lll',
            ))
        ;

        if ($this->admin) {
            $this->columnBuilder
                ->add('compte.user.username', 'column', array(
                    'title' => 'PG',
                ))
            ;
        } else {
            $this->columnBuilder
                ->add('compte.boquette.nom', 'column', array(
                    'title' => 'Boquette',
                ))
            ;
        }

        $this->columnBuilder
            ->add('moyenPaiement', 'column', array(
                'title' => 'Moyen',
            ))
            ->add('infos', 'column', array(
                'title' => 'Infos',
            ))
            ->add('montant', 'column', array(
                'title' => 'Montant',
            ))
            ->add('status', 'column', array(
                'title' => 'Statut',
                'visible' => false,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $formatter = function ($line) {
            $line['montant'] = $this->intranetExt->prixFilter($line['montant']);
            $line['moyenPaiement'] = $this->intranetExt->moyenPaiementFilter($line['moyenPaiement']);
            if ($line['status'] != 'OK') {
                $line['infos'] = 'Annulé ! Erreur : '.$line['status'].' / '.$line['infos'];
            }

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Transaction';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'credits_datatable';
    }
}
