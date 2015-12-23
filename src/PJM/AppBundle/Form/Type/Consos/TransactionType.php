<?php

namespace PJM\AppBundle\Form\Type\Consos;

use PJM\AppBundle\Enum\TransactionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityRepository;

class TransactionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $enum = new TransactionEnum();
        $moyenPaiementsChoices = $enum->getMoyenPaiementChoices(true);
        unset($moyenPaiementsChoices['smoney']);
        unset($moyenPaiementsChoices['lydia']);
        unset($moyenPaiementsChoices['initial']);
        unset($moyenPaiementsChoices['event']);

        $builder
            ->add('comptes', 'pjm_select2_entity', array(
                'label' => 'Destinataire(s)',
                'class' => 'PJMAppBundle:Compte',
                'error_bubbling' => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('c')
                        ->where('c.boquette = :boquette')
                        ->join('c.user', 'u')
                        ->orderBy('u.fams', 'ASC')
                        ->addOrderBy('u.proms', 'DESC')
                        ->setParameter('boquette', $options['boquette'])
                        ;
                },
                'choice_label' => 'user',
                'multiple' => true,
            ))
            ->add('compteLie', 'pjm_select2_entity', array(
                'label' => 'Transfert vers (optionnel)',
                'help_label' => 'Si renseigné, les différents crédits seront transférés ensuite à cette personne.',
                'class' => 'PJMAppBundle:Compte',
                'error_bubbling' => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('c')
                        ->where('c.boquette = :boquette')
                        ->join('c.user', 'u')
                        ->orderBy('u.fams', 'ASC')
                        ->addOrderBy('u.proms', 'DESC')
                        ->setParameter('boquette', $options['boquette'])
                    ;
                },
                'empty_value' => "Choisir un receveur",
                'choice_label' => 'user',
                'required' => false,
            ))
            ->add('moyenPaiement', 'choice', array(
                'label' => 'Moyen de paiement',
                'error_bubbling' => true,
                'choices' => $moyenPaiementsChoices,
            ))
            ->add('infos', null, array(
                'label' => 'Infos',
                'help_label' => 'N° de chèque, ou raison en cas d\'opération',
                'error_bubbling' => true,
                'constraints' => array(
                    new Assert\Length(array(
                        'min' => 1,
                        'max' => 250,
                    )),
                ),
            ))
            ->add('montant', 'money', array(
                'label' => 'Montant',
                'help_label' => 'Positif pour un crédit, négatif pour un débit',
                'error_bubbling' => true,
                'divisor' => 100,
                'constraints' => array(
                    new Assert\LessThanOrEqual(array(
                        'value' => 200 * 100,
                        'message' => 'Pas plus de 200€ par crédit. Fais en plusieurs.',
                    )),
                ),
            ))
            ->add('save', 'submit', array(
                'label' => 'Créditer',
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Transaction',
            'boquette' => null,
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
