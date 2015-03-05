<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class AnnuaireDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class AnnuaireDatatable extends AbstractDatatableView
{
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
            ->setOrder(array("column" => 4, "direction" => "desc"))
            ->setPageLength(25)
        ;

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_profil_annuaireResults'));

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('username', 'column', array(
                'title' => 'Username',
                'visible' => false
            ))
            ->add('bucque', 'column', array(
                'title' => 'Bucque'
            ))
            ->add('fams', 'column', array(
                'title' => "Fam's"
            ))
            ->add('tabagns', 'column', array(
                'title' => "Tabagn's"
            ))
            ->add('proms', 'column', array(
                'title' => "Prom's"
            ))
            ->add('prenom', 'column', array(
                'title' => 'Prénom'
            ))
            ->add('nom', 'column', array(
                'title' => 'Nom'
            ))
            ->add('telephone', 'column', array(
                'title' => 'Téléphone'
            ))
            ->add('appartement', 'column', array(
                'title' => "K'gib"
            ))
            ->add('classe', 'column', array(
                'title' => 'Classe'
            ))
            ->add('anniversaire', 'datetime', array(
                'title' => 'Anniversaire',
                'format' => 'll'
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = new IntranetExtension();
        $formatter = function($line) use($ext) {
            $line["tabagns"] = $ext->tabagnsFilter($line["tabagns"]);
            $line["telephone"] = $ext->telephoneFilter($line["telephone"]);

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\UserBundle\Entity\User';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'annuaire_datatable';
    }
}
