<?php

namespace PJM\AppBundle\Form\Type\Actus;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ArticleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', 'text')
            ->add('contenu', "froala")
            ->add('categories', 'pjm_select2_entity', array(
                'label' => 'Catégories',
                'class'    => 'PJMAppBundle:Actus\Categorie',
                'property' => 'nom',
                'multiple' => true,
                'required' => false
            ))
            ->add('publication', 'checkbox', array(
                'label' => 'Décocher pour enregistrer en tant que brouillon',
                'required' => false
            ))
            ->add('save', 'submit', array(
                'label' => $options['ajout'] ? 'Ajouter' : 'Modifier',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Actus\Article',
            'ajout' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_pjm_appbundle_actus_articletype';
    }
}
