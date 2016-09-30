<?php

namespace PJM\AppBundle\Form\Type\Actus;

use PJM\AppBundle\Form\Type\Boquette\BoquetteByResponsableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', 'text')
            ->add('contenu', 'textarea', array(
                'required' => false,
                'attr' => array(
                    'class' => 'tinymce',
                ),
            ))
            ->add('categories', 'genemu_jqueryselect2_entity', array(
                'label' => 'Catégories',
                'class' => 'PJMAppBundle:Actus\Categorie',
                'choice_label' => 'nom',
                'multiple' => true,
                'required' => false,
            ))
            ->add('boquette', new BoquetteByResponsableType(array('user' => $options['user'])))
            ->add('publication', 'checkbox', array(
                'label' => 'Décocher pour enregistrer en tant que brouillon',
                'required' => false,
            ))
            ->add('save', 'submit', array(
                'label' => $options['ajout'] ? 'Ajouter' : 'Modifier',
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Actus\Article',
            'ajout' => true,
            'user' => null,
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
