<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class PaniersDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class PaniersDatatable extends AbstractDatatableView
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        $this->getFeatures()->setServerSide(false);

        $this->getOptions()
            ->setOrder(array("column" => 0, "direction" => "desc"))
        ;

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'format' => 'll'
            ))
            ->add('infos', 'column', array('title' => 'Contenu',))
            ->add('prix', 'column', array('title' => 'Prix',))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = new IntranetExtension();
        $formatter = function($line) use($ext) {
            $line["prix"] = $ext->prixFilter($line["prix"]);
            $line["infos"] = json_decode($line["infos"]);
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
        return 'historique_paniers_datatable';
    }
}
