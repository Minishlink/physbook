<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class HistoriqueAdminDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class HistoriqueAdminDatatable extends AbstractDatatableView
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

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_app_admin_consos_brags_bucquagesResults'));

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
            ->add('user.username', 'column', array('title' => 'PG',))
            ->add('item.libelle', 'column', array('title' => 'Item',))
            ->add('nombre', 'column', array('title' => 'Nombre',))
            ->add('item.prix', 'column', array('title' => 'P.U.',))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = new IntranetExtension();
        $formatter = function($line) use($ext) {
            $line["item"]["prix"] = $ext->prixFilter($line["item"]["prix"]);
            $line["nombre"] = $ext->nombreFilter($line["nombre"]);
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
