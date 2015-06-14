<?php

namespace PJM\AppBundle\Form\Type\Consos;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints as Assert;

class PrixBaguetteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prix', 'money', array(
                'error_bubbling' => true,
                'divisor' => 100,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\LessThan(array(
                        'value' => 200,
                        'message' => 'Le prix de la baguette ne devrait pas être plus grand que 2€.'
                    )),
                )
            ))
            ->add('save', 'submit', array(
                'label' => 'Modifier',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Item'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_boquette_brags_prixBaguette';
    }
}
