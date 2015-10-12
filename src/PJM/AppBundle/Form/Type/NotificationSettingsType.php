<?php

namespace PJM\AppBundle\Form\Type;

use PJM\AppBundle\Enum\Notifications\NotificationSettingsEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationSettingsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $enum = new NotificationSettingsEnum();
        $subscriptionsChoices = $enum->getSubscriptionsChoices(true);

        $builder
            ->add('subscriptions', 'pjm_select2_choice', array(
                'label' => 'Abonnements',
                'choices' => $subscriptionsChoices,
                'required' => false,
                'multiple' => true,
            ))
            ->add('save', 'submit', array(
                'label' => 'Sauvegarder',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Notifications\NotificationSettings',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_notificationSettings';
    }
}
