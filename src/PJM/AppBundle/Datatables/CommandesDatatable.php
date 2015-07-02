<?php

namespace PJM\AppBundle\Datatables;

/**
 * Class CommandesDatatable.
 */
class CommandesDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        parent::buildDatatable();

        $this->options->setOption('order', [[8, 'asc']]);

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('pjm_app_admin_boquette_brags_commandesResults'),
        ));

        $this->columnBuilder
            ->add(null, 'multiselect', array(
                'actions' => array(
                    array(
                        'route' => 'pjm_app_admin_boquette_brags_validerCommandes',
                        'label' => 'Valider',
                        'icon' => 'glyphicon glyphicon-ok',
                        'attributes' => array(
                            'class' => 'btn btn-default btn-xs',
                            'role' => 'button'
                        ),
                    ),
                    array(
                        'route' => 'pjm_app_admin_boquette_brags_resilierCommandes',
                        'label' => 'Résilier',
                        'icon' => 'glyphicon glyphicon-remove',
                        'attributes' => array(
                            'class' => 'btn btn-default btn-xs',
                            'role' => 'button'
                        ),
                    ),
                ),
                'width' => '20px',
            ))
            ->add('date', 'datetime', array(
                'title' => 'Création',
                'date_format' => 'll',
            ))
            ->add('dateDebut', 'datetime', array(
                'title' => 'Début',
                'date_format' => 'll',
            ))
            ->add('dateFin', 'datetime', array(
                'title' => 'Fin',
                'date_format' => 'll',
            ))
            ->add('user.username', 'column', array('title' => 'PG'))
            ->add('user.appartement', 'column', array('title' => 'Kagib'))
            ->add('nombre', 'column', array('title' => 'Nombre'))
            ->add('item.prix', 'column', array('title' => 'P.U.'))
            ->add('valid', 'column', array('title' => 'État'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $formatter = function ($line) {
            $line['item']['prix'] = $this->intranetExt->prixFilter($line['item']['prix']);
            $line['nombre'] = $this->intranetExt->nombreFilter($line['nombre']);
            $line['valid'] = $this->intranetExt->validCommandeFilter($line['valid']);

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
