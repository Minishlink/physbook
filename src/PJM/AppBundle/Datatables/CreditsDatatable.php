<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class CreditsDatatable.
 */
class CreditsDatatable extends AbstractDatatableView
{
    protected $ajaxUrl;
    protected $admin;

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
    public function buildDatatableView()
    {
        $this->getFeatures()
            ->setServerSide(true)
            ->setProcessing(true)
        ;

        $this->getOptions()
            ->setOrder(array('column' => 0, 'direction' => 'desc'))
        ;

        $this->getAjax()->setUrl($this->ajaxUrl);

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('date', 'datetime', array(
                'title' => 'Date ISO',
                'format' => '',
                'visible' => false,
            ))
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'format' => 'lll',
            ))
        ;

        if ($this->admin) {
            $this->getColumnBuilder()
                ->add('compte.user.username', 'column', array(
                    'title' => 'PG',
                ))
            ;
        } else {
            $this->getColumnBuilder()
                ->add('compte.boquette.nom', 'column', array(
                    'title' => 'Boquette',
                ))
            ;
        }

        $this->getColumnBuilder()
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
        $ext = new IntranetExtension();
        $formatter = function ($line) use ($ext) {
            $line['montant'] = $ext->prixFilter($line['montant']);
            $line['moyenPaiement'] = $ext->moyenPaiementFilter($line['moyenPaiement']);
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
