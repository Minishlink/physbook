<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;

/**
 * Class ResponsableDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class ResponsableDatatable extends AbstractDatatableView
{
    protected $boquetteSlug;

    public function setBoquetteSlug($boquetteSlug)
    {
        $this->boquetteSlug = $boquetteSlug;
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
            ->setOrder(array("column" => 3, "direction" => "desc"))
        ;

        $this->getAjax()->setUrl(
            $this->getRouter()->generate('pjm_app_admin_responsablesResults', array(
                'boquette_slug' => $this->boquetteSlug
            ))
        );

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getMultiselect()
            ->setEnabled(true)
            ->setPosition("last")
            ->addAction("Activer/Désactiver", "pjm_app_admin_toggleResponsables")
            ->setWidth("20px")
        ;

        $this->getColumnBuilder()
            ->add("user.bucque", "column", array("visible" => false))
            ->add('user.username', 'column', array(
                'title' => 'Utilisateur',
            ))
            ->add('responsabilite.libelle', 'column', array(
                "title" => "Rôle",
            ))
            ->add("active", "boolean", array(
                "title" => "Actif",
                "true_icon" => "glyphicon glyphicon-ok",
                "false_icon" => "glyphicon glyphicon-remove",
                "true_label" => "Oui",
                "false_label" => "Non"
            ))
            ->add("date", "datetime", array(
                "title" => "Créé",
                "format" => "ll"
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $formatter = function($line){
            $line["user"]["username"] = $line["user"]["bucque"]." ".$line["user"]["username"];

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Responsable';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'responsables_datatable';
    }
}
