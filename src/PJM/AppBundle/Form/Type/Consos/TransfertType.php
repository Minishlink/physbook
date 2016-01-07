<?php

namespace PJM\AppBundle\Form\Type\Consos;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class TransfertType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('emetteur', null, array(
                'label' => 'Depuis quel compte ?',
                'class' => 'PJMAppBundle:Compte',
                'query_builder' => function (EntityRepository $er) use ($options) {
                   return $er->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $options['user'])
                    ;
                },
                'choice_label' => 'boquette',
            ))
            ->add('receveurUser', 'genemu_jqueryselect2_entity', array(
                'label' => 'A qui ?',
                'class' => 'PJMAppBundle:User',
                'query_builder' => function (EntityRepository $er) use ($options) {
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
                'label' => 'Transférer',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Consos\Transfert',
            'user' => null,
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
