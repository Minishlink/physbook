<?php

namespace PJM\AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NewUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', 'checkbox', array(
                'label' => 'Activé',
                'required' => false
            ))
            ->add('email', 'email')
            ->add('fams', 'text')
            ->add('tabagns', 'choice', array(
                'choices' => array(
                    "bo" => "Bordel's",
                    "li" => "Birse",
                    "an" => "Boquette",
                    "me" => "Siber's",
                    "ch" => "Chalon's",
                    "cl" => "Clun's",
                    "ai" => "KIN",
                    "ka" => "Kanak",
                    "pa" => "Paris",
                )))
            ->add('proms', 'text')
            ->add('bucque', 'text')
            ->add('prenom', 'text')
            ->add('nom', 'text')
            ->add('telephone', 'text', array('required' => false))
            ->add('appartement', 'text', array('required' => false))
            ->add('classe', 'text', array('required' => false))
            ->add('anniversaire', 'text', array('required' => false))
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
        return 'pjm_appbundle_admin_new_user_form';
    }
}
