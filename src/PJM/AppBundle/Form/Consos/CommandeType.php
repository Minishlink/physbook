<?php

namespace PJM\AppBundle\Form\Consos;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Choice;

class CommandeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
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
                        'message' => "Choisis un nombre de baguettes par jour valide."
                    ))
                )
            ))
            ->add('save', 'submit', array(
                'label' => 'Modifier',
            ))
        ;
    }

    /** Get nombre de baguettes par jour possibles
     *
     * @return array
     */
    public static function getRangeBaguettes($keys = false)
    {
        // min, max, step
        $range = range(0, 5, 0.5);

        if ($keys) {
            return array_combine($range, $range);
        }

        return $range;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Commande'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_consos_brags_commande';
    }
}
