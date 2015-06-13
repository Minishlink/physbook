<?php

namespace PJM\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class Select2EntityType extends AbstractType
{
    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'pjm_select2_entity';
    }
}
