<?php

namespace PJM\AppBundle\Datatables;

/**
 * Class AnnuaireDatatable.
 */
class AnnuaireDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable($locale = null)
    {
        parent::buildDatatable($locale);

        $this->options->setOption('order', [[2, 'asc']]);
        $this->options->setOption('page_length', 25);
        $this->options->setOption('individual_filtering', true);

        $this->ajax->set(array(
            'url' => $this->router->generate('pjm_profil_annuaireResults'),
        ));

        $this->columnBuilder
            ->add(null, 'action', array(
                'title' => 'Actions',
                'width' => '20px',
                'actions' => array(
                    array(
                        'route' => 'pjm_profil_voir',
                        'route_parameters' => array(
                            'username' => 'username',
                        ),
                        'label' => '',
                        'icon' => 'glyphicon glyphicon-user',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'Voir son profil',
                            'class' => 'btn btn-default btn-xs',
                            'role' => 'button',
                        ),
                    ),
                    array(
                        'route' => 'pjm_app_inbox_nouveauMessage',
                        'route_parameters' => array(
                            'user' => 'username',
                        ),
                        'label' => '',
                        'icon' => 'glyphicon glyphicon-comment',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'Lui envoyer un message',
                            'class' => 'btn btn-default btn-xs',
                            'role' => 'button',
                        ),
                    ),
                ),
            ))
            ->add('username', 'column', array(
                'title' => 'Username',
                'visible' => false,
            ))
            ->add('bucque', 'column', array(
                'title' => 'Bucque',
            ))
            ->add('nums', 'column', array(
                'title' => "Num's",
            ))
            ->add('tabagns', 'column', array(
                'title' => "Tabagn's",
            ))
            ->add('proms', 'column', array(
                'title' => "Prom's",
            ))
            ->add('prenom', 'column', array(
                'title' => 'Prénom',
            ))
            ->add('nom', 'column', array(
                'title' => 'Nom',
            ))
            ->add('telephone', 'column', array(
                'title' => 'Téléphone',
            ))
            ->add('appartement', 'column', array(
                'title' => "K'gib",
            ))
            ->add('classe', 'column', array(
                'title' => 'Classe',
            ))
            ->add('anniversaire', 'datetime', array(
                'title' => 'Anniversaire',
                'date_format' => 'll',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $formatter = function ($line) {
            foreach ($line as &$l) {
                if (gettype($l) == 'string') {
                    $l = htmlentities($l);
                }
            }

            $line['tabagns'] = $this->intranetExt->tabagnsFilter($line['tabagns']);
            $line['nums'] = implode('-', $line['nums']);

            if ($line['telephone'] != '') {
                $line['telephone'] = '<a href="tel:'.$line['telephone'].'" title="Appeler">'.$this->intranetExt->telephoneFilter($line['telephone']).'</a>';
            }

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\User';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'annuaire_datatable';
    }
}
