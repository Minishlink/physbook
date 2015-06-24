<?php

namespace PJM\AppBundle\Datatables;

use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class CommandesDatatable.
 */
class CommandesDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        $this->options->setOption('order', [[7, 'asc']]);

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('pjm_app_admin_boquette_brags_commandesResults'),
        ));

        $this->columnBuilder
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
            ->add(null, 'multiselect', array(
                'action' => array(
                    'route' => 'pjm_app_admin_boquette_brags_validerCommandes',
                    'label' => 'Valider',
                    'icon' => 'glyphicon glyphicon-ok',
                ),
                'action' => array(
                    'route' => 'pjm_app_admin_boquette_brags_resilierCommandes',
                    'label' => 'Résilier',
                    'icon' => 'glyphicon glyphicon-remove',
                ),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = new IntranetExtension();
        $formatter = function ($line) use ($ext) {
            $line['item']['prix'] = $ext->prixFilter($line['item']['prix']);
            $line['nombre'] = $ext->nombreFilter($line['nombre']);
            $line['valid'] = $ext->validCommandeFilter($line['valid']);

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
