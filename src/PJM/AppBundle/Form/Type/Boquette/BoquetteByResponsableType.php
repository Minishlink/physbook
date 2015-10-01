<?php

namespace PJM\AppBundle\Form\Type\Boquette;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class BoquetteByResponsableType extends AbstractType
{
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = array(
            'label' => 'De la part d\'une boquette ?',
            'class' => 'PJMAppBundle:Boquette',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('b')
                    ->join('b.responsabilites', 'r')
                    ->join('r.responsables', 're')
                    ->where('re.user = :user')
                    ->andWhere('re.active = true')
                    ->setParameter(':user', $this->options['user'])
                ;
            },
            'choice_label' => 'nom',
            'help_label' => "Ta boquette n'apparaît pas alors que tu en es responsable ? Contacte un ZiPhy'sbook.",
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
