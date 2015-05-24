<?php

namespace PJM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateTimePickerType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_merge($view->vars, array(
            'linkedTo' => $options['linkedTo']
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy HH:mm',
            'linkedTo' => null
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
