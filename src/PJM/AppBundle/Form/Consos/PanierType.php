<?php

namespace PJM\AppBundle\Form\Consos;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Validator\Constraints as Assert;

class PanierType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', 'date', array(
                'error_bubbling' => true,
                'label' => 'Date de livraison du panier',
            ))
            ->add('infos', 'textarea', array(
                'error_bubbling' => true,
                'label' => 'Contenu du panier',
                'attr' => array('placeholder' => 'une ligne par fruit ou légume')
            ))
            ->add('prix', 'money', array(
                'error_bubbling' => true,
                'divisor' => 100,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\LessThan(array(
                        'value' => 3000,
                        'message' => 'Le prix du panier devrait pas être plus grand que 30€.'
                    )),
                ),
                'data' => '500'
            ))
            ->add('save', 'submit', array(
                'label' => 'Ajouter',
            ))
            ->addEventListener(FormEvents::PRE_BIND, function (FormEvent $event) {
                $panier = $event->getData();
                if (isset($panier['infos'])) {
                    $panier['infos'] = preg_split("/\r\n|\n|\r/", $panier['infos']);
                }
                $event->setData($panier);
            })
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PJM\AppBundle\Entity\Item'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pjm_appbundle_boquette_paniers_listePaniers';
    }
}
