<?php

namespace PJM\AppBundle\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class FeaturedItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item', 'pjm_select2_entity', array(
                'error_bubbling' => true,
                'label' => 'Item',
                'class' => 'PJMAppBundle:Item',
                'query_builder' => function(EntityRepository $er) use ($options) {
                   return $er->createQueryBuilder('i')
                        ->where('i.boquette = :boquette')
                        ->andWhere('i.valid = true')
                        ->orderBy('i.libelle', 'ASC')
                        ->setParameter(':boquette', $options['boquette'])
                    ;
                },
            ))
            ->add('save', 'submit', array(
                'label' => 'Ajouter',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\FeaturedItem',
            'boquette' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_admin_featureditem';
    }
}
