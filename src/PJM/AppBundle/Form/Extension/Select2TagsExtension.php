<?php

namespace PJM\AppBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Select2TagsExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ((array_key_exists('tags', $options['configs']) && !$options['configs']['tags']) || !$options['multiple']) {
            return;
        }

        if (!$options['listened_set']) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $field = $event->getForm();
                $options = $field->getConfig()->getAttributes()['data_collector/passed_options'];
                $form = $field->getParent();
                $data = $event->getData();

                if (!empty($data)) {
                    $options['listened_set'] = true;
                    $options['choices'] = array_combine($data, $data);
                    $options['choices_as_values'] = true;

                    $form->remove($field->getName());
                    $form->add($field->getName(), $this->getExtendedType(), $options);
                }
            });
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            // submit
            $field = $event->getForm();
            $options = $field->getConfig()->getAttributes()['data_collector/passed_options'];
            $form = $field->getParent();
            $data = $event->getData();

            if (array_key_exists('listened_submit', $options) && $options['listened_submit']) {
                // it's a new submit
                // event data is empty, we have to set it again
                $event->setData($options['listened_submit']);
                return;
            }

            if (empty($data)) {
                return;
            }

            $options['listened_submit'] = $data;
            $options['choices'] = $data;
            $options['choices_as_values'] = true;

            $form->remove($field->getName());
            $form->add($field->getName(), $this->getExtendedType(), $options);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // we need both options for preventing infinite loops
        $resolver->setDefaults(array(
            'listened_set' => false,
            'listened_submit' => false,
        ));
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'genemu_jqueryselect2_choice';
    }
}
