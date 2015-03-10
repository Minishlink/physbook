<?php

namespace PJM\AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PJM\AppBundle\Form\ImageType;

class BoquetteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('slug')
            ->add('image', new ImageType(), array(
                'label' => 'Logo',
                'required' => false
            ))
            ->add('caisseSMoney', null, array(
                'label' => 'Caisse S-Money',
            ))
            ->add('save', 'submit', array(
                'label' => 'Envoyer',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Boquette'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_pjm_appbundle_admin_boquette';
    }
}
