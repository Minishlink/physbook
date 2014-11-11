<?php

namespace PJM\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add your custom field
        $builder->remove('username');
        $builder->add('prenom', 'text');
        $builder->add('nom', 'text');
        $builder->add('promo', 'number');
    }

    public function getName()
    {
        return 'pjm_user_registration';
    }
}
