<?php

namespace PJM\AppBundle\Form\Boquette;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class BoquetteByResponsableType extends AbstractType
{
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_merge($view->vars, array(
            'help_label' => $options['help_label']
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $defaults = array(
            'label' => 'De la part d\'une boquette ?',
            'class'    => 'PJMAppBundle:Boquette',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('b')
                    ->join('b.responsabilites', 'r')
                    ->join('r.responsables', 're')
                    ->where('re.user = :user')
                    ->andWhere('re.active = true')
                    ->setParameter(':user', $this->options['user'])
                ;
            },
            'property' => 'nom',
            'help_label' => "Ta boquette n'apparaît pas alors tu en es responsable ? Contacte un ZiPhy'sbook."
        );

        if (!empty($this->options['required'])) {
            $defaults['label'] = 'De la part de';
        } else {
            $defaults['empty_value'] = 'Non';
            $defaults['empty_data'] = null;
            $defaults['required'] = false;
        }

        $resolver->setDefaults($defaults);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boquetteByResponsableType';
    }
}
