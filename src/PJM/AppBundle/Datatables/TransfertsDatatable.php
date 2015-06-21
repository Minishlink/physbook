<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class TransfertsDatatable.
 */
class TransfertsDatatable extends AbstractDatatableView
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
