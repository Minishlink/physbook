<?php

namespace PJM\AppBundle\Form\Consos;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class TransfertType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('emetteur', null, array(
                'label' => 'Depuis quel compte ?',
                'class' => 'PJMAppBundle:Compte',
                'query_builder' => function(EntityRepository $er) use ($options) {
                   return $er->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $options['user'])
                    ;
                },
                'property' => 'boquette'
            ))
            ->add('receveurUser', 'pjm_select2_entity', array(
                'label' => 'A qui ?',
                'class' => 'PJMUserBundle:User',
                'query_builder' => function(EntityRepository $er) use ($options) {
                   return $er->getQbAllButUser($options['user']);
                },
            ))
            ->add('montant', 'money', array(
                'label' => 'Montant',
                'divisor' => 100,
            ))
            ->add('raison', null, array(
                'label' => 'Raison',
            ))
            ->add('save', 'submit', array(
                'label' => 'Transférer'
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Consos\Transfert',
            'user' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_consos_transfert';
    }
}
