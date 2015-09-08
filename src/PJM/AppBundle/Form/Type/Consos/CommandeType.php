<?php

namespace PJM\AppBundle\Form\Type\Consos;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Choice;

class CommandeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', 'choice', array(
                'error_bubbling' => true,
                'label' => 'Nombre par jour',
                'choices' => $this->getRangeBaguettes(true),
                'constraints' => array(
                    new NotBlank(),
                    new Choice(array(
                        'choices' => $this->getRangeBaguettes(),
                        'message' => 'Choisis un nombre de baguettes par jour valide.',
                    )),
                ),
            ))
            ->add('save', 'submit', array(
                'label' => 'Modifier',
            ))
        ;
    }

    /** Get nombre de baguettes par jour possibles
     * @return array
     */
    public static function getRangeBaguettes($withValues = false)
    {
        $choices = array(
            '0' => 'Aucune baguette',
            '0.5' => 'Une demi-baguette',
            '1' => 'Une baguette',
            '1.5' => '1.5 baguettes',
            '2' => '2 baguettes',
            '2.5' => '2.5 baguettes',
            '3' => '3 baguettes',
        );

        if ($withValues) {
            return $choices;
        }

        return array_keys($choices);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Commande',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_boquette_brags_commande';
    }
}
