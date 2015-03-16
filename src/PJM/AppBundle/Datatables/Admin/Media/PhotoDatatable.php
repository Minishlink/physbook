<?php

namespace PJM\AppBundle\Datatables\Admin\Media;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class PhotoDatatable
 *
 * @package PJM\AppBundle\Datatables
 */
class PhotoDatatable extends AbstractDatatableView
{
    protected $twigExt;

    public function setTwigExt(IntranetExtension $twigExt)
    {
        $this->twigExt = $twigExt;
    }

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
            ->setOrder(array("column" => 0, "direction" => "desc"))
        ;

        $this->getAjax()->setUrl(
            $this->getRouter()->generate('pjm_app_admin_media_photosResults')
        );

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

         $this->getMultiselect()
            ->setEnabled(true)
            ->setPosition("last")
            ->addAction("Autoriser", "pjm_app_admin_media_autoriserPhotos")
            ->addAction("Ne pas autoriser", "pjm_app_admin_media_pasAutoriserPhotos")
            ->addAction("Supprimer", "pjm_app_admin_media_supprimerPhotos")
            ->setWidth("20px")
        ;

        $this->getColumnBuilder()
            ->add('date', 'datetime', array(
                'title' => 'Date ISO',
                'format' => '',
                'visible' => false
            ))
            ->add("image.id", "column", array("visible" => false))
            ->add('image.ext', "column", array("visible" => false))
            ->add('image.alt', 'column', array(
                'title' => 'Photo',
            ))
            ->add('legende', 'column', array(
                'title' => 'Légende'
            ))
            ->add('proprietaire.username', 'column', array(
                'title' => 'Propriétaire',
            ))
            ->add('date', 'datetime', array(
                'title' => 'Date',
                'format' => 'll'
            ))
            ->add("publication", "column", array(
                'title' => 'Publication',
            ))
            ->add(null, "action", array(
                "title" => "Actions",
                "actions" => array(
                    array(
                        "route" => "pjm_app_admin_media_gestionPhotos",
                        "route_parameters" => array(
                            "photo" => "id"
                        ),
                        "label" => "Modifier",
                        "icon" => "glyphicon glyphicon-edit",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Modifier",
                            "class" => "btn btn-default btn-xs",
                            "role" => "button"
                        ),
                    ),
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $ext = $this->twigExt;
        $formatter = function($line) use($ext) {
            $line["image"]["alt"] = $ext->imageFunction($line["image"]["id"], $line["image"]["ext"], $line["image"]["alt"]);
            $line["publication"] = $ext->etatPublicationPhotoFilter($line["publication"]);

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
