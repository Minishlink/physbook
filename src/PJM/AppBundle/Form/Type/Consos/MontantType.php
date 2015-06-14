<?php

namespace PJM\AppBundle\Form\Type\Consos;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MontantType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('montant', 'money', array(
                'label' => 'Montant',
                'error_bubbling' => true,
                'divisor' => 100,
                'constraints' => array(
                    new Assert\LessThan(array(
                        'value' => 200*100,
                        'message' => 'Pas plus de 200€ par transaction. Fais en plusieurs.'
                    )),
                    new Assert\GreaterThan(array(
                        'value' => 0,
                        'message' => "Le montant doit être supérieur à 0€."
                    )),
                )
            ))
            ->add('save', 'submit', array(
                'label' => 'Recharger',
                'attr' => array(
                    'class' => 'btn-primary',
                )
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Transaction'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_montant';
    }
}
