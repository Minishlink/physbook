<?php

namespace PJM\AppBundle\Enum\Media;

/**
 * PhotoEnum.
 */
class PhotoEnum
{
    public static function getPublicationChoices($withValues = false)
    {
        $choices = array(
            '0' => 'En attente de validation',
            '1' => 'Pas autorisée',
            '2' => 'Autorisée',
            '3' => 'Affichée',
        );

        if ($withValues) {
            return $choices;
        }

        return array_keys($choices);
    }
}
