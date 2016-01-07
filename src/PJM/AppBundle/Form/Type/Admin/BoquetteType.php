<?php

namespace PJM\AppBundle\Form\Type\Admin;

use PJM\AppBundle\Entity\Boquette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PJM\AppBundle\Form\Type\ImageType;

class BoquetteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $couleursEnum = new \PJM\AppBundle\Enum\CouleursEnum();
        $couleursChoices = $couleursEnum->getCouleursChoices(true);

        $builder
            ->add('nom')
            ->add('slug')
            ->add('lieux', 'genemu_jqueryselect2_choice', array(
                'configs' => array(
                    'tags' => true,
                ),
                'multiple' => true,
                'required' => false,
            ))
            ->add('image', new ImageType(), array(
                'label' => 'Logo',
                'required' => false,
            ))
            ->add('caisseSMoney', null, array(
                'label' => 'Caisse S-Money',
            ))
            ->add('couleur', 'choice', array(
                'choices' => $couleursChoices,
                'required' => false,
            ))
            ->add('save', 'submit', array(
                'label' => 'Envoyer',
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Boquette',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_pjm_appbundle_admin_boquette';
    }
}
