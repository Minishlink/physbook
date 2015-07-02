<?php

namespace PJM\AppBundle\Datatables;

use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class AchatsDatatable.
 */
class AchatsDatatable extends BaseDatatable
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

        $this->ajax->setOptions(array(
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
