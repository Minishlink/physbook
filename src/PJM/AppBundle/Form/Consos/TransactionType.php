<?php

namespace PJM\AppBundle\Form\Consos;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityRepository;

class TransactionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $enum = new \PJM\AppBundle\Enum\TransactionEnum();
        $moyenPaiementsChoices = $enum->getMoyenPaiementChoices(true);
        unset($moyenPaiementsChoices['smoney']);
        unset($moyenPaiementsChoices['initial']);

        $builder
            ->add('compte', 'pjm_select2_entity', array(
                'label' => 'Destinataire',
                'class'    => 'PJMAppBundle:Compte',
                'error_bubbling' => true,
                'query_builder' => function(EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('c')
                        ->where('c.boquette = :boquette')
                        ->join('c.user', 'u')
                        ->orderBy('u.fams', 'ASC')
                        ->addOrderBy('u.proms', 'DESC')
                        ->setParameter('boquette', $options['boquette'])
                    ;
                },
                'property' => 'user'
            ))
            ->add('compteLie', 'pjm_select2_entity', array(
                'label' => 'De la part de',
                'class'    => 'PJMAppBundle:Compte',
                'error_bubbling' => true,
                'query_builder' => function(EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('c')
                        ->where('c.boquette = :boquette')
                        ->join('c.user', 'u')
                        ->orderBy('u.fams', 'ASC')
                        ->addOrderBy('u.proms', 'DESC')
                        ->setParameter('boquette', $options['boquette'])
                    ;
                },
                'empty_value' => "Choisir le vrai créditeur, s'il y a lieu",
                'property' => 'user',
                'required' => false
            ))
            ->add('moyenPaiement', 'choice', array(
                'label' => 'Moyen de paiement',
                'error_bubbling' => true,
                'choices' => $moyenPaiementsChoices
            ))
            ->add('infos', null, array(
                'label' => 'Infos (n° de chèque/raison)',
                'error_bubbling' => true,
                'constraints' => array(
                    new Assert\Length(array(
                        'min' => 1,
                        'max' => 250
                    )),
                )
            ))
            ->add('montant', 'money', array(
                'label' => 'Montant',
                'error_bubbling' => true,
                'divisor' => 100,
                'constraints' => array(
                    new Assert\LessThanOrEqual(array(
                        'value' => 200*100,
                        'message' => 'Pas plus de 200€ par crédit. Fais en plusieurs.'
                    )),
                )
            ))
            ->add('save', 'submit', array(
                'label' => 'Créditer',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Transaction',
            'boquette' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_transaction';
    }
}
