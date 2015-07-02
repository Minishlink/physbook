<?php

namespace PJM\AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use Sg\DatatablesBundle\Datatable\View\Style;

/**
 * Class BaseDatatable.
 */
abstract class BaseDatatable extends AbstractDatatableView
{
    public function buildDatatable()
    {
        $this->options->setOptions(array(
            'order' => [[0, 'desc']],
            'class' => Style::BOOTSTRAP_3_STYLE,
            'use_integration_options' => true,
            'responsive' => true,
        ));
    }
}
