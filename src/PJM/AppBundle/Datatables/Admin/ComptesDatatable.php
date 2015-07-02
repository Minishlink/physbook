<?php

namespace PJM\AppBundle\Datatables\Admin;

use PJM\AppBundle\Datatables\BaseDatatable;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class ComptesDatatable.
 */
class ComptesDatatable extends BaseDatatable
{
    private $intranetExt;
    protected $boquetteSlug;

    public function setIntranetExt(IntranetExtension $intranetExt)
    {
        $this->intranetExt = $intranetExt;
    }

    public function setBoquetteSlug($boquetteSlug)
    {
        $this->boquetteSlug = $boquetteSlug;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        parent::buildDatatable();

        $this->options->setOption('order', [[2, 'asc']]);

        if (isset($this->boquetteSlug)) {
            $this->ajax->setOptions(array(
                'url' => $this->router->generate('pjm_app_admin_boquette_comptesResults', array(
                    'boquette_slug' => $this->boquetteSlug,
                )),
            ));
        }

        $this->columnBuilder
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
            $line['solde'] = $this->intranetExt->prixFilter($line['solde']);

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
