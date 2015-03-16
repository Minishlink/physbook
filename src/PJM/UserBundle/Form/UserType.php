<?php

namespace PJM\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use PJM\AppBundle\Form\Media\PhotoType;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bucque')
            ->add('fams')
            ->add('email')
            ->add('prenom')
            ->add('nom')
            ->add('telephone')
            ->add('appartement')
            ->add('classe')
            ->add('anniversaire', 'birthday')
            ->add('save', 'submit', array(
                'label' => 'Mettre à jour'
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_userbundle_user';
    }
}
