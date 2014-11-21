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
        if (strlen($string) > 2 || $string == "0") {
            $unit = "€";
        }

        return $string.$unit;
    }

    public function getName()
    {
        return 'intranet_extension';
    }
}
