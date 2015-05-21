<?php

namespace PJM\AppBundle\Form\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use PJM\AppBundle\Form\ImageType;

class EvenementType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('description', null, array(
                'required' => false
            ))
            ->add('isJournee', null, array(
                'label' => 'Journée(s) entière(s) ?',
                'required' => false
            ))
            ->add('dateDebut', 'datetime', array(
                'label' => 'Date de début',
                'required' => false
            ))
            ->add('dateFin', 'datetime', array(
                'label' => 'Date de fin',
                'required' => false
            ))
            ->add('lieu')
            ->add('isPublic', null, array(
                'label' => 'Evènement public',
                'required' => false
            ))
            ->add('image', new ImageType(), array(
                'label' => 'Image',
                'required' => false
            ))
            ->add('save', 'submit', array(
                'label' => 'Transférer'
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Event\Evenement'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_event_evenement';
    }
}
