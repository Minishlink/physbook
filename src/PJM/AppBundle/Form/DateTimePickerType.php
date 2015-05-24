<?php

namespace PJM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateTimePickerType extends AbstractType
{
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy HH:mm'
        ));
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'datetime';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'datetimePicker';
    }
}
