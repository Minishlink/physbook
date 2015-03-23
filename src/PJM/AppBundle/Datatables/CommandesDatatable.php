<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class CommandesDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class CommandesDatatable extends AbstractDatatableView
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
            ->setOrder(array("column" => 8, "direction" => "asc"));

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_app_admin_boquette_brags_commandesResults'));

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getMultiselect()
            ->setEnabled(true)
            ->setPosition("last")
            ->addAction("Valider", "pjm_app_admin_boquette_brags_validerCommandes")
            ->addAction("Résilier", "pjm_app_admin_boquette_brags_resilierCommandes")
            ->setWidth("20px")
        ;

        $this->getColumnBuilder()
            ->add('date', 'datetime', array(
                'title' => 'Date ISO',
                'format' => '',
                'visible' => false
            ))
            ->add('date', 'datetime', array(
                'title' => 'Création',
                'format' => 'll'
            ))
            ->add('dateDebut', 'datetime', array(
                'title' => 'Début',
                'format' => 'll'
            ))
            ->add('dateFin', 'datetime', array(
                'title' => 'Fin',
                'format' => 'll'
            ))
            ->add('user.username', 'column', array('title' => 'PG',))
            ->add('user.appartement', 'column', array('title' => 'Kagib',))
            ->add('nombre', 'column', array('title' => 'Nombre',))
            ->add('item.prix', 'column', array('title' => 'P.U.',))
            ->add('valid', 'column', array('title' => 'État',))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = new IntranetExtension();
        $formatter = function($line) use ($ext) {
            $line["item"]["prix"] = $ext->prixFilter($line["item"]["prix"]);
            $line["nombre"] = $ext->nombreFilter($line["nombre"]);
            $line["valid"] = $ext->validCommandeFilter($line["valid"]);
            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Commande';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'brags_commandes_datatable';
    }
}
