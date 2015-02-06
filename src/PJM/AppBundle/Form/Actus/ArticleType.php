<?php

namespace PJM\AppBundle\Form\Actus;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use PJM\AppBundle\Form\ImageType;

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
            ->add('contenu', 'textarea')
            ->add('image', new ImageType(), array('required' => false))
            ->add('categories', 'genemu_jqueryselect2_entity', array(
                'class'    => 'PJMAppBundle:Actus\Categorie',
                'property' => 'nom',
                'multiple' => true,
                'required' => false
            ))
            ->add('save', 'submit', array(
                'label' => $options['ajout'] ? 'Ajouter' : 'Modifier',
            ))
        ;

        $factory = $builder->getFormFactory();

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event) use ($factory) {
                $article = $event->getData();
                if (null === $article) {
                    return;
                }

                if (false === $article->getPublication()) {
                    $event->getForm()->add(
                        $factory->createNamed(
                            'publication',
                            'checkbox',
                            null,
                            array('required' => false, 'auto_initialize' => false)
                        )
                    );
                } else {
                    $event->getForm()->remove('publication');
                }
            }
        );
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
