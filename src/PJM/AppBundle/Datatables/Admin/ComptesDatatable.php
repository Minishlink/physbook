<?php

namespace PJM\AppBundle\Datatables\Admin;

use PJM\AppBundle\Datatables\BaseDatatable;

/**
 * Class ComptesDatatable.
 */
class ComptesDatatable extends BaseDatatable
{
    protected $boquetteSlug;

    public function setBoquetteSlug($boquetteSlug)
    {
        $this->boquetteSlug = $boquetteSlug;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatable($locale = null)
    {
        parent::buildDatatable($locale);

        $this->options->setOption('order', [[2, 'asc']]);

        if (isset($this->boquetteSlug)) {
            $this->ajax->set(array(
                'url' => $this->router->generate('pjm_app_admin_boquette_comptesResults', array(
                    'slug' => $this->boquetteSlug,
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
