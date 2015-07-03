<?php

namespace PJM\AppBundle\Datatables\Boquette;

use PJM\AppBundle\Datatables\BaseDatatable;
use PJM\AppBundle\Services\Image as ImageService;

/**
 * Class ItemDatatable.
 */
class ItemDatatable extends BaseDatatable
{
    private $imageExt;
    protected $boquetteSlug;

    public function setImageExt(ImageService $imageExt)
    {
        $this->imageExt = $imageExt;
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

        $this->options->setOption('order', [[6, 'desc']]);

        if (isset($this->boquetteSlug)) {
            $this->ajax->setOptions(array(
                'url' => $this->router->generate('pjm_app_boquette_itemResults', array(
                    'boquette_slug' => $this->boquetteSlug,
                )),
            ));
        }

        $this->columnBuilder
            ->add('boquette.slug', 'column', array('visible' => false))
            ->add('image.id', 'column', array('visible' => false))
            ->add('image.ext', 'column', array('visible' => false))
            ->add('image.alt', 'column', array(
                'title' => 'Image',
            ))
            ->add('libelle', 'column', array(
                'title' => 'Nom',
            ))
            ->add('prix', 'column', array(
                'title' => 'Prix',
            ))
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'll',
            ))
            ->add('valid', 'boolean', array(
                'title' => 'Actif',
                'true_icon' => 'glyphicon glyphicon-ok',
                'false_icon' => 'glyphicon glyphicon-remove',
                'true_label' => 'Oui',
                'false_label' => 'Non',
            ))
        ;

        if ($this->admin) {
            $this->columnBuilder
                ->add(null, 'action', array(
                    'title' => 'Actions',
                    'actions' => array(
                        array(
                            'route' => 'pjm_app_admin_boquette_modifierItem',
                            'route_parameters' => array(
                                'boquette' => 'boquette.slug',
                                'item' => 'id',
                            ),
                            'label' => 'Modifier',
                            'icon' => 'glyphicon glyphicon-edit',
                            'attributes' => array(
                                'rel' => 'tooltip',
                                'title' => 'Modifier',
                                'class' => 'btn btn-default btn-xs',
                                'role' => 'button',
                            ),
                        ),
                    ),
                ))
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $formatter = function ($line) {
            $line['prix'] = $this->intranetExt->prixFilter($line['prix']);
            $line['image']['alt'] = !empty($line['image']['id']) ?
                $this->imageExt->html($line['image']['id'], $line['image']['ext'], $line['image']['alt']) :
                "Pas d'image";

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
        return 'pjm_app_boquette_item_datatable';
    }
}
