<?php

namespace PJM\AppBundle\Datatables\Admin;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class ComptesDatatable.
 */
class ComptesDatatable extends AbstractDatatableView
{
    protected $boquetteSlug;
    protected $twigExt;

    public function setBoquetteSlug($boquetteSlug)
    {
        $this->boquetteSlug = $boquetteSlug;
    }

    public function setTwigExt(IntranetExtension $twigExt)
    {
        $this->twigExt = $twigExt;
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
            ->setOrder(array('column' => 2, 'direction' => 'asc'))
        ;

        $this->getAjax()->setUrl(
            $this->getRouter()->generate('pjm_app_admin_boquette_comptesResults', array(
                'boquette_slug' => $this->boquetteSlug,
            ))
        );

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add('user.bucque', 'column', array('visible' => false))
            ->add('user.username', 'column', array(
                'title' => 'Utilisateur',
            ))
            ->add('solde', 'column', array(
                'title' => 'Solde',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $formatter = function ($line) {
            $line['user']['username'] = $line['user']['bucque'].' '.$line['user']['username'];
            $line['solde'] = $this->twigExt->prixFilter($line['solde']);

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Compte';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'comptes_datatable';
    }
}
