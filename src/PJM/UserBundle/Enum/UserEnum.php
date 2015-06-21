<?php

namespace PJM\UserBundle\Enum;

/**
 * UserEnum.
 */
class UserEnum
{
    public static function getTabagnsChoices($withValues = false)
    {
        $choices = array(
            'bo' => "Bordel's",
            'li' => 'Birse',
            'an' => 'Boquette',
            'me' => "Siber's",
            'ch' => "Chalon's",
            'cl' => "Clun's",
            'ai' => 'KIN',
            'ka' => "K'nak",
            'pa' => 'P2',
        );

        if ($withValues) {
            return $choices;
        }

        return array_keys($choices);
    }

    public static function getGenreChoices($withValues = false)
    {
        $choices = array(
            1 => 'Féminin',
            0 => 'Masculin',
        );

        if ($withValues) {
            return $choices;
        }

        return array_keys($choices);
    }
}
