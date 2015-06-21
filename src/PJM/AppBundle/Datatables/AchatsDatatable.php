<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class AchatsDatatable.
 */
class AchatsDatatable extends AbstractDatatableView
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
            ->setProcessing(true);

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

        if (!$this->admin) {
            $this->getColumnBuilder()
                ->add('item.boquette.nom', 'column', array('title' => 'Boquette'))
            ;
        } else {
            $this->getColumnBuilder()
                ->add('user.username', 'column', array('title' => 'PG'))
            ;
        }

        $this->getColumnBuilder()
            ->add('item.libelle', 'column', array('title' => 'Item'))
            ->add('nombre', 'column', array('title' => 'Nombre'))
            ->add('item.prix', 'column', array('title' => 'Prix'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = new IntranetExtension();
        $formatter = function ($line) use ($ext) {
            $line['nombre'] = $ext->nombreFilter($line['nombre']);
            $line['item']['prix'] = $ext->prixFilter($line['nombre'] * $line['item']['prix']);

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
