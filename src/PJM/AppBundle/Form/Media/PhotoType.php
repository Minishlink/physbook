<?php

namespace PJM\AppBundle\Form\Media;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use PJM\AppBundle\Form\ImageType;

class PhotoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['ajout'] || $options['proprietaire'] == 'admin') {
            $builder->add('image', new ImageType(), array(
                'required' => $options['ajout']
            ));
        }

        $builder
            ->add('legende', null, array(
                'label' => 'Légende',
                'required' => false
            ))
        ;

        $choices = array(
            '1' => "Pas autorisée",
            '2' => "Autorisée",
        );
        if ($options['admin']) {
            $choices['3'] = "Affichée";
        }

        $builder
            ->add('publication', 'choice', array(
                'label' => "Publication sur Bonjour Gadz'Arts",
                'choices' => $choices
            ))
        ;

        $builder->add('save', 'submit', array(
            'label' => 'Sauvegarder'
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Media\Photo',
            'ajout' => true,
            'admin' => false,
            'proprietaire' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_media_photo';
    }
}
