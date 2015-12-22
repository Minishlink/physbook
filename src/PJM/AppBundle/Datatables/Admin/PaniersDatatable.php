<?php

namespace PJM\AppBundle\Datatables\Admin;

use PJM\AppBundle\Datatables\BaseDatatable;

/**
 * Class PaniersDatatable.
 */
class PaniersDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable($locale = null)
    {
        parent::buildDatatable($locale);

        $this->ajax->set(array(
            'url' => $this->router->generate('pjm_app_admin_boquette_paniers_paniersResults'),
        ));

        $this->columnBuilder
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'll',
            ))
            ->add('infos', 'column', array('title' => 'Infos'))
            ->add('prix', 'column', array('title' => 'Prix'))
            ->add('valid', 'boolean', array(
                'title' => 'Actif',
                'true_icon' => 'glyphicon glyphicon-ok',
                'false_icon' => 'glyphicon glyphicon-remove',
                'true_label' => 'Oui',
                'false_label' => 'Non',
            ))
            ->add(null, 'action', array(
                'title' => 'Commandes',
                'actions' => array(
                    array(
                        'route' => 'pjm_app_admin_boquette_paniers_voirCommandes',
                        'route_parameters' => array(
                            'panier' => 'id',
                        ),
                        'label' => 'État',
                        'icon' => 'glyphicon glyphicon-eye-open',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => "Voir l'état des commandes",
                            'class' => 'btn btn-default btn-xs',
                            'role' => 'button',
                        ),
                    ),
                    array(
                        'route' => 'pjm_app_admin_boquette_paniers_telechargerCommandes',
                        'route_parameters' => array(
                            'panier' => 'id',
                        ),
                        'label' => 'Stopper et télécharger',
                        'icon' => 'glyphicon glyphicon-save',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'Télécharger et arrêter les commandes (.xlsx)',
                            'class' => 'btn btn-default btn-xs',
                            'role' => 'button',
                        ),
                        'confirm' => true,
                        'confirm_message' => 'Attention, cela va stopper les prises de commandes pour ce panier si elles ne le sont pas encore. Es-tu sûr ?',
                    ),
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
            $line['prix'] = $this->intranetExt->prixFilter($line['prix']);

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Item';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'historique_datatable';
    }
}
