<?php

namespace PJM\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReglagesNotificationsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $enum = new \PJM\AppBundle\Enum\ReglagesNotificationsEnum();
        $actusChoices = $enum->getActusChoices(true);
        $banqueChoices = $enum->getBanqueChoices(true);

        $builder
            ->add('actus', 'pjm_select2_choice', array(
                'label' => 'Actualités',
                'choices' => $actusChoices,
                'required'  => false,
                'multiple' => true
            ))
            ->add('events', null, array(
                'label' => "Évènements",
                'required' => false
            ))
            ->add('messages', null, array(
                'label' => "Messages",
                'required' => false
            ))
            ->add('banque', 'pjm_select2_choice', array(
                'label' => 'Banque',
                'choices' => $banqueChoices,
                'required'  => false,
                'multiple' => true
            ))
            ->add('save', 'submit', array(
                'label' => 'Sauvegarder'
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\ReglagesNotifications'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_reglagesnotifications';
    }
}
