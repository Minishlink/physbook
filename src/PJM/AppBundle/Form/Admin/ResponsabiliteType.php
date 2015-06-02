<?php

namespace PJM\AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ResponsabiliteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('boquette', 'pjm_select2_entity', array(
                'label' => 'Boquette',
                'class' => 'PJMAppBundle:Boquette',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('b')
                        ->orderBy('b.nom', 'ASC');
                },
            ))
            ->add('libelle', null, array(
                'label' => 'Libellé',
            ))
            ->add('role', null, array(
                'label' => 'Rôle',
            ))
            ->add('niveau', null, array(
                'attr' => array('placeholder' => "ex. 0")
            ))
            ->add('active', null, array(
                'required' => false
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
            'data_class' => 'PJM\AppBundle\Entity\Responsabilite'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_admin_responsabilite';
    }
}
