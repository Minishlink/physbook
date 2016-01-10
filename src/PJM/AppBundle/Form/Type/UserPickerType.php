<?php

namespace PJM\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class UserPickerType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', 'entity', array(
                'label' => $options['label_users'],
                'class' => 'PJMAppBundle:User',
                'multiple' => true,
                'required' => false,
                'apply_filter' => false,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('u')
                        ->orderBy('u.fams', 'ASC')
                        ->addOrderBy('u.proms', 'DESC')
                    ;

                    if (!empty($options['notIncludeUsers'])) {
                        $qb
                            ->where('u NOT IN (:users)')
                            ->setParameter('users', $options['notIncludeUsers'])
                        ;
                    }

                    return $qb;
                },
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'label_users' => 'Utilisateurs',
            'notIncludeUsers' => array(),
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_userPicker';
    }
}
