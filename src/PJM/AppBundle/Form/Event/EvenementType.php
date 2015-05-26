<?php

namespace PJM\AppBundle\Form\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use PJM\AppBundle\Form\ImageType;

class EvenementType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $boquetteOptions = array(
            'label' => 'De la part d\'une boquette ?',
            'class'    => 'PJMAppBundle:Boquette',
            'query_builder' => function(EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('b')
                    ->join('b.responsabilites', 'r')
                    ->join('r.responsables', 're')
                    ->where('re.user = :user')
                    ->andWhere('re.active = true')
                    ->setParameter(':user', $options['user'])
                ;
            },
            'property' => 'nom',
            'empty_value' => 'Non',
            'empty_data' => null,
            'required' => false,
        );

        $builder
            ->add('nom')
            ->add('description', null, array(
                'required' => false
            ))
            ->add('isJournee', null, array(
                'label' => 'Journée(s) entière(s) ?',
                'required' => false
            ))
            ->add('dateDebut', 'datetimePicker', array(
                'label' => 'Date de début',
                'required' => false
            ))
            ->add('dateFin', 'datetimePicker', array(
                'label' => 'Date de fin',
                'required' => false,
                'linkedTo' => 'dateDebut'
            ))
            ->add('lieu')
            ->add('prix', 'money', array(
                'label' => 'Prix',
                'divisor' => 100
            ))
            ->add('boquette', 'entity', $boquetteOptions)
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
            'data_class' => 'PJM\AppBundle\Entity\Event\Evenement',
            'user' => null,
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
