<?php

namespace PJM\AppBundle\Datatables;

use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class TransfertsDatatable.
 */
class TransfertsDatatable extends BaseDatatable
{
    private $intranetExt;
    protected $ajaxUrl;
    protected $admin;

    public function setIntranetExt(IntranetExtension $intranetExt)
    {
        $this->intranetExt = $intranetExt;
    }

    public function setAjaxUrl($ajaxUrl)
    {
        $this->ajaxUrl = $ajaxUrl;
    }

    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        parent::buildDatatable();

        $this->options->setOption('individual_filtering', true);

        $this->ajax->setOptions(array(
            'url' => $this->ajaxUrl ? $this->ajaxUrl : '',
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
        $formatter = function ($line) {
            $line['montant'] = $this->intranetExt->prixFilter($line['montant']);
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
