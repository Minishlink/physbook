<?php

namespace PJM\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileParserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', $options['parserType'], array(
                'label' => 'Fichier',
            ))
            ->add('verifier', 'submit', array(
                'label' => 'Vérifier',
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'parserType' => 'file',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'fileParserType';
    }
}
