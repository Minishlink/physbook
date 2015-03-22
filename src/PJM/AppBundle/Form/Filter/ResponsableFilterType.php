<?php
namespace PJM\AppBundle\Form\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Doctrine\ORM\EntityRepository;

class ResponsableFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('responsabilite', 'filter_entity', array(
                'label' => 'Responsabilité',
                'class' => 'PJMAppBundle:Responsabilite',
                'query_builder' => function(EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('r')
                        ->orderBy('r.libelle', 'ASC')
                    ;
                },
                'multiple' => true,
                'required' => false
            ))
            ->add('active', 'filter_boolean', array(
                'label' => 'Actif',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering')
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_pjm_appbundle_responsablefiltertype';
    }
}
