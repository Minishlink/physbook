<?php

namespace PJM\AppBundle\Form\Consos;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class CommandeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', 'number', array(
                'constraints' => array(
                    new NotBlank(),
                    new Range(array(
                        'min' => 0.5,
                        'max' => 5
                    ))
                )
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Historique'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_consos_brags_commande';
    }
}
