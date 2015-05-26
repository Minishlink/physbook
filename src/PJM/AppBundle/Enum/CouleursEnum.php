<?php

namespace PJM\AppBundle\Enum;

/**
 * CouleursEnum
 *
 * Enumerations des couleurs possibles, c'est-à-dire celles dans site/background.less
 */
class CouleursEnum
{
    public static function getCouleursChoices($withValues = false) {
        $choices = array(
            'blanc' => "Blanc",
            'noir' => "Noir",
            'gris' => "Gris",
            'orange' => "Orange",
            'jaune' => "Jaune",
            'bleu' => "Bleu",
            'vert' => "Vert",
            'mauve' => "Mauve",
            'rose' => "Rose"
        );

        if ($withValues) {
            return $choices;
        }

        return array_keys($choices);
    }
}
