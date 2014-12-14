<?php

namespace PJM\AppBundle\Form\Actus;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

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
            ->add('auteur', 'text')
            ->add('image', new ImageType(), array('required' => false))
            ->add('categories', 'entity', array(
                'class'    => 'PJMAppBundle:Actus\Categorie',
                'property' => 'nom',
                'multiple' => true,
                'required' => false));

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
            'data_class' => 'PJM\AppBundle\Entity\Actus\Article'
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
