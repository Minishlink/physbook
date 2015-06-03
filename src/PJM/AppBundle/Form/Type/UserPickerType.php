<?php

namespace PJM\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilderInterface;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuter;
use PJM\AppBundle\Form\Filter\ResponsableFilterType;

class UserPickerType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', 'entity', array(
                'label' => $options['label_users'],
                'class' => 'PJMUserBundle:User',
                'multiple' => true,
                'required' => false,
                'apply_filter' => false
            ));

        $userEnum = new \PJM\UserBundle\Enum\UserEnum();

        $builder
            ->add('fams', 'filter_text', array(
                'label' => "Fam's",
                'condition_pattern' => FilterOperands::STRING_EQUALS
            ))
            ->add('tabagns', 'filter_choice', array(
                'label' => "Tabagn's",
                'choices' => $userEnum->getTabagnsChoices(true)
            ))
            ->add('proms', 'filter_number', array(
                'label' => "Prom's"
            ))
            ->add('appartement', 'filter_text', array(
                'label' => 'Etage/Kagib',
                'condition_pattern' => FilterOperands::STRING_BOTH
            ))
            ->add('classe', 'filter_text', array(
                'label' => 'Classe',
                'condition_pattern' => FilterOperands::STRING_BOTH
            ))
            ->add('genre', 'filter_choice', array(
                'label' => "Genre",
                'choices' => $userEnum->getGenreChoices(true)
            ))
            ->add('responsables', 'filter_collection_adapter', array(
                'label' => false,
                'type'      => new ResponsableFilterType(),
                'add_shared' => function (\Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuter $qbe)  {
                    $closure = function(QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                        $filterBuilder->leftJoin($alias . '.responsables', $joinAlias);
                    };

                    $qbe->addOnce($qbe->getAlias().'.responsables', 're', $closure);
                },
            ))
            ->add('filtre', 'submit', array(
                'label' => "Inviter",
                'attr' => array(
                    'class' => 'btn-primary'
                )
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label_users' => 'Utilisateurs'
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