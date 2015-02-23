<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class HistoriqueDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class HistoriqueDatatable extends AbstractDatatableView
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        $this->getFeatures()
            ->setServerSide(true)
            ->setProcessing(true);

        $this->getOptions()
            ->setOrder(array("column" => 0, "direction" => "desc"))
        ;

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_app_consos_historiqueResults'));

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('date', 'datetime', array(
                'title' => 'Date ISO',
                'format' => '',
                'visible' => false
            ))
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'format' => 'll'
            ))
            ->add('nombre', 'column', array('title' => 'Nombre',))
            ->add('item.libelle', 'column', array('title' => 'Item',))
            ->add('item.prix', 'column', array('title' => 'Prix',))
            ->add('item.boquette.nom', 'column', array('title' => 'Boquette',))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = new IntranetExtension();
        $formatter = function($line) use($ext) {
            $line["nombre"] = $ext->nombreFilter($line["nombre"]);
            $line["item"]["prix"] = $ext->prixFilter($line["nombre"]*$line["item"]["prix"]);
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
        return 'historique_datatable';
    }
}
