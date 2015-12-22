<?php

namespace PJM\AppBundle\Datatables\Admin\Media;

use PJM\AppBundle\Datatables\BaseDatatable;
use PJM\AppBundle\Services\Image as ImageService;

/**
 * Class PhotoDatatable.
 */
class PhotoDatatable extends BaseDatatable
{
    private $imageExt;

    public function setImageExt(ImageService $imageExt)
    {
        $this->imageExt = $imageExt;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatable($locale = null)
    {
        parent::buildDatatable($locale);

        $this->options->setOption('order', [[1, 'desc']]);

        $this->ajax->set(array(
            'url' => $this->router->generate('pjm_app_admin_media_photosResults'),
        ));

        $this->columnBuilder
            ->add(null, 'multiselect', array(
                'actions' => array(
                    array(
                        'route' => 'pjm_app_admin_media_autoriserPhotos',
                        'label' => 'Autoriser',
                        'icon' => 'glyphicon glyphicon-ok-circle',
                        'attributes' => array(
                            'class' => 'btn btn-default btn-xs',
                            'role' => 'button',
                        ),
                    ),
                    array(
                        'route' => 'pjm_app_admin_media_pasAutoriserPhotos',
                        'label' => 'Ne pas autoriser',
                        'icon' => 'glyphicon glyphicon-ban-circle',
                        'attributes' => array(
                            'class' => 'btn btn-default btn-xs',
                            'role' => 'button',
                        ),
                    ),
                    array(
                        'route' => 'pjm_app_admin_media_supprimerPhotos',
                        'label' => 'Supprimer',
                        'icon' => 'glyphicon glyphicon-remove-circle',
                        'attributes' => array(
                            'class' => 'btn btn-danger btn-xs',
                            'role' => 'button',
                        ),
                    ),
                ),
                'width' => '20px',
            ))
            ->add('image.id', 'column', array('visible' => false))
            ->add('image.ext', 'column', array('visible' => false))
            ->add('image.alt', 'column', array(
                'title' => 'Photo',
            ))
            ->add('legende', 'column', array(
                'title' => 'Légende',
            ))
            ->add('proprietaire.username', 'column', array(
                'title' => 'Propriétaire',
            ))
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'll',
            ))
            ->add('publication', 'column', array(
                'title' => 'Publication',
            ))
            ->add('usersHM.users.username', 'array', array(
                'title' => "Phy's HM Users",
                'data' => 'usersHM.users[, ].username',
                'visible' => false,
            ))
            ->add('usersHM.id', 'virtual', array(
                'title' => "Phy's HM",
            ))
            ->add(null, 'action', array(
                'title' => 'Actions',
                'actions' => array(
                    array(
                        'route' => 'pjm_app_admin_media_gestionPhotos',
                        'route_parameters' => array(
                            'photo' => 'id',
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

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $formatter = function ($line) {
            $line['image']['alt'] = $this->imageExt->html($line['image']['id'], $line['image']['ext'], $line['image']['alt']);
            $line['publication'] = $this->intranetExt->etatPublicationPhotoFilter($line['publication']);
            $line['usersHM']['id'] = count($line['usersHM']['users']);

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'PJM\AppBundle\Entity\Media\Photo';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pjm_app_media_photo_datatable';
    }
}
