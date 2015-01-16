<?php

namespace PJM\AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ResponsableType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'genemu_jqueryselect2_entity', array(
                'error_bubbling' => true,
                'label' => 'Utilisateur',
                'class' => 'PJMUserBundle:User',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC');
                },
            ))
            ->add('responsabilite', 'genemu_jqueryselect2_entity', array(
                'error_bubbling' => true,
                'label' => 'Responsabilité',
                'class' => 'PJMAppBundle:Responsabilite',
                'query_builder' => function(EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('r')
                        ->where('r.boquette = :boquette')
                        ->andWhere('r.active = true')
                        ->orderBy('r.libelle', 'ASC')
                        ->setParameter(':boquette', $options['boquette'])
                    ;
                },
            ))
            ->add('active', null, array(
                'label' => 'Actif',
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
            'data_class' => 'PJM\AppBundle\Entity\Responsable',
            'boquette' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_admin_responsable';
    }
}
