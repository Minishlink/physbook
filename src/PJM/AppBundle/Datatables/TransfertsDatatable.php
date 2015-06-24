<?php

namespace PJM\AppBundle\Datatables;

use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class TransfertsDatatable.
 */
class TransfertsDatatable extends BaseDatatable
{
    protected $admin;

    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        parent::buildDatatableView();

        $this->ajax->setOptions(array(
            'url' => 'pjm_app_banque_transfertsResults',
        ));

        $this->columnBuilder
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'lll',
            ))
            ->add('emetteur.boquette.nom', 'column', array(
                'title' => 'Boquette',
            ))
            ->add('emetteur.user.username', 'column', array(
                'title' => 'Emetteur',
            ))
            ->add('receveur.user.username', 'column', array(
                'title' => 'Receveur',
            ))
            ->add('montant', 'column', array(
                'title' => 'Montant',
            ))
            ->add('raison', 'column', array(
                'title' => 'Infos',
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
        $ext = new IntranetExtension();
        $formatter = function ($line) use ($ext) {
            $line['montant'] = $ext->prixFilter($line['montant']);
            if ($line['status'] != 'OK') {
                $line['raison'] = 'Annulé ! Erreur : '.$line['status'].' / '.$line['raison'];
            }
            $line['raison'] = htmlentities($line['raison']);

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Consos\Transfert';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'transferts_datatable';
    }
}
