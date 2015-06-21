<?php

namespace PJM\AppBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $enum = new \PJM\AppBundle\Enum\TransactionEnum();
        $moyenPaiementsChoices = $enum->getMoyenPaiementChoices(true);

        $builder
            ->add('date', 'filter_date_range', array(
                'label' => 'Date (intervalle ouvert)',
                'left_date_options' => array(
                    'label' => 'De',
                ),
                'right_date_options' => array(
                    'label' => 'A',
                ),
            ))
            ->add('moyenPaiement', 'filter_choice', array(
                'label' => 'Moyen de paiement',
                'choices' => $moyenPaiementsChoices,
            ))
            ->add('export', 'submit', array(
                'label' => 'Exporter',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('filtering'),
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
