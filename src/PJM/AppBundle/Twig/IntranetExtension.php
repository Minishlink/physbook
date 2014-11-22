<?php

namespace PJM\AppBundle\Twig;

class IntranetExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('prix', array($this, 'prixFilter')),
        );
    }

    public function prixFilter($string)
    {
        $unit = "c";
        $price = (float) $string / 100; // centimes => euros
        if (strlen($string) > 2 || $string == "0") {
            $unit = "€";
            $string = number_format($price, 2, ',', ' ');
        }

        return $string.$unit;
    }

    public function getName()
    {
        return 'intranet_extension';
    }
}
