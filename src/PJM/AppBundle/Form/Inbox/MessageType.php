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
        $boquetteOptions = array(
            'label' => 'De la part d\'une boquette ?',
            'class'    => 'PJMAppBundle:Boquette',
            'error_bubbling' => true,
            'query_builder' => function(EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('b')
                    ->join('b.responsabilites', 'r')
                    ->join('r.responsables', 're')
                    ->where('re.user = :user')
                    ->andWhere('re.active = true')
                    ->andWhere('r.niveau <= 1')
                    ->setParameter(':user', $options['user'])
                ;
            },
            'property' => 'nom',
        );

        if (!$options['annonce']) {
            $boquetteOptions['empty_value'] = 'Non';
            $boquetteOptions['empty_data'] = null;
            $boquetteOptions['required'] = false;
        } else {
            $boquetteOptions['label'] = 'De la part de';
        }

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
            ->add('boquette', 'entity', $boquetteOptions)
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
            'annonce' => false
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
