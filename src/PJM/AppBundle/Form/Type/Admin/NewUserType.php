<?php

namespace PJM\AppBundle\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userEnum = new \PJM\AppBundle\Enum\UserEnum();

        $builder
            ->add('email', 'email')
            ->add('fams', 'text')
            ->add('tabagns', 'choice', array(
                'choices' => $userEnum->getTabagnsChoices(true),
            ))
            ->add('proms', 'text')
            ->add('genre', 'choice', array(
                'choices' => array_reverse($userEnum->getGenreChoices(true)), // plus fréquent d'inscrire des hommes
            ))
            ->add('bucque', 'text')
            ->add('prenom', 'text')
            ->add('nom', 'text')
            ->add('telephone', 'text', array('required' => false))
            ->add('appartement', 'text', array('required' => false))
            ->add('classe', 'text', array('required' => false))
            ->add('anniversaire', 'text', array('required' => false))
            ->add('enabled', 'checkbox', array(
                'label' => 'Activé',
                'required' => false,
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
            'data_class' => 'PJM\AppBundle\Entity\User',
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
