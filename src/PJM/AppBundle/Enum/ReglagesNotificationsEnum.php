<?php

namespace PJM\AppBundle\Enum;

/**
 * ReglagesNotificationsEnum.
 */
class ReglagesNotificationsEnum
{
    public static function getActusChoices($withValues = false)
    {
        $choices = array(
            'centre' => "Tabagn's",
            'promotion' => "Prom's",
            'assos' => 'Boquettes',
            'maj' => 'Mises à jour',
            'generales' => 'Générales',
        );

        if ($withValues) {
            return $choices;
        }

        return array_keys($choices);
    }

    public static function getDefaultActusChoices()
    {
        return array(
            'centre',
            'promotion',
            'assos',
            'maj',
        );
    }

    public static function getBanqueChoices($withValues = false)
    {
        $choices = array(
            'negatif' => "Négat's",
            'credits' => 'Crédts par transfert',
            'debits' => 'Débits spéciaux',
        );

        if ($withValues) {
            return $choices;
        }

        return array_keys($choices);
    }

    public static function getDefaultBanqueChoices()
    {
        return array(
            'negatif',
        );
    }
}
