<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use Sg\DatatablesBundle\Datatable\View\Style;
use PJM\AppBundle\Twig\IntranetExtension;

/**
 * Class BaseDatatable.
 */
abstract class BaseDatatable extends AbstractDatatableView
{
    protected $intranetExt;
    protected $ajaxUrl;
    protected $admin;

    public function setIntranetExt(IntranetExtension $intranetExt)
    {
        $this->intranetExt = $intranetExt;
    }

    public function setAjaxUrl($ajaxUrl)
    {
        $this->ajaxUrl = $ajaxUrl;
    }

    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    public function buildDatatable($locale = null)
    {
        $this->options->set(array(
            'order' => [[0, 'desc']],
            'class' => Style::BOOTSTRAP_3_STYLE,
            'use_integration_options' => true,
        ));

        $this->features->set(array(
            'extensions' => array(
                'responsive' => true,
            ),
        ));
    }
}
