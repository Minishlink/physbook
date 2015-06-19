<?php
namespace PJM\AppBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

class CompteFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', new UserFilterType(), array(
                'label' => 'Utilisateur',
                'simple' => true
            ))
            ->add('solde', 'filter_number', array(
                'condition_operator' => FilterOperands::OPERAND_SELECTOR
            ))
            ->add('export', 'submit', array(
                'label' => 'Exporter'
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
        return 'form_pjm_appbundle_transactionfiltertype';
    }
}
