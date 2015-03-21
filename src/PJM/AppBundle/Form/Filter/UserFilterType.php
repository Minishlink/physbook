<?php
namespace PJM\AppBundle\Form\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuter;

class UserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userEnum = new \PJM\UserBundle\Enum\UserEnum();

        $builder
            ->add('proms', 'filter_number', array(
                'label' => "Prom's",
            ))
            ->add('tabagns', 'filter_choice', array(
                'label' => "Tabagn's",
                'choices' => $userEnum->getTabagnsChoices(true)
            ))
            ->add('fams', 'filter_text', array(
                'label' => "Fam's",
                'condition_pattern' => FilterOperands::OPERAND_SELECTOR
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
                'type'      => new ResponsableFilterType(),
                'add_shared' => function (\Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuter $qbe)  {
                    $closure = function(QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                        // add the join clause to the doctrine query builder
                        // the where clause for the label and color fields will be added automatically with the right alias later by the Lexik\Filter\QueryBuilderUpdater
                        $filterBuilder->leftJoin($alias . '.responsables', $joinAlias);
                    };

                    // then use the query builder executor to define the join, the join's alias and things to do on the doctrine query builder.
                    $qbe->addOnce($qbe->getAlias().'.responsables', 'opt', $closure);
                },
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering')
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_pjm_appbundle_userfiltertype';
    }
}
