<?php

namespace PJM\AppBundle\Form\Inbox;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use PJM\AppBundle\Form\ImageType;

use Doctrine\ORM\EntityRepository;

class MessageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('destinations', 'genemu_jqueryselect2_entity', array(
                'label' => 'Destinataires',
                'class'    => 'PJMAppBundle:Inbox\Inbox',
                'error_bubbling' => true,
                'query_builder' => function(EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('i')
                        ->join('i.user', 'u')
                        ->orderBy('u.fams', 'ASC')
                        ->addOrderBy('u.proms', 'DESC')
                    ;
                },
                'multiple' => true,
                'property' => 'user'
            ))
            ->add('contenu', "textarea", array(
                'attr'=> array('style' => 'min-height: 20em;')
            ))
            ->add('boquette', 'entity', array(
                'label' => 'De la part d\'une boquette ?',
                'class'    => 'PJMAppBundle:Boquette',
                'error_bubbling' => true,
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
                'empty_data'  => null,
                'required' => false
            ))
            ->add('save', 'submit', array(
                'label' => 'Envoi',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Inbox\Message',
            'user' => null,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_pjm_appbundle_inbox_messagetype';
    }
}
