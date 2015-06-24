<?php

namespace PJM\AppBundle\Datatables;

/**
 * Class ResponsableDatatable.
 */
class ResponsableDatatable extends BaseDatatable
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
        $this->options->setOption('order', [[3, 'desc']]);

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('pjm_app_admin_boquette_responsablesResults', array(
                'boquette_slug' => $this->boquetteSlug,
            )),
        ));

        $this->columnBuilder
            ->add('user.bucque', 'column', array('visible' => false))
            ->add('user.username', 'column', array(
                'title' => 'Utilisateur',
            ))
            ->add('responsabilite.libelle', 'column', array(
                'title' => 'Rôle',
            ))
            ->add('active', 'boolean', array(
                'title' => 'Actif',
                'true_icon' => 'glyphicon glyphicon-ok',
                'false_icon' => 'glyphicon glyphicon-remove',
                'true_label' => 'Oui',
                'false_label' => 'Non',
            ))
            ->add('date', 'datetime', array(
                'title' => 'Créé',
                'date_format' => 'll',
            ))
            ->add(null, 'multiselect', array(
                'action' => array(
                    'route' => 'pjm_app_admin_boquette_toggleResponsables',
                    'label' => 'Activer/Désactiver',
                    'icon' => 'glyphicon glyphicon-pencil',
                ),
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
