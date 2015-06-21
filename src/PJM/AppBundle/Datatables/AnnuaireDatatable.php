<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class AnnuaireDatatable.
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
            ->setOrder(array('column' => 2, 'direction' => 'asc'))
            ->setPageLength(25)
        ;

        $this->getAjax()->setUrl($this->getRouter()->generate('pjm_profil_annuaireResults'));

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->getColumnBuilder()
            ->add(null, 'action', array(
                'title' => 'Actions',
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
            ->add('fams', 'column', array(
                'title' => "Fam's",
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
                'format' => 'll',
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
            foreach ($line as &$l) {
                if (gettype($l) == 'string') {
                    $l = htmlentities($l);
                }
            }

            $line['tabagns'] = $ext->tabagnsFilter($line['tabagns']);

            if ($line['telephone'] != '') {
                $line['telephone'] = '<a href="tel:'.$line['telephone'].'" title="Appeler">'.$ext->telephoneFilter($line['telephone']).'</a>';
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
