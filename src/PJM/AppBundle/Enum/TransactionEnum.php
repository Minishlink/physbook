<?php

namespace PJM\AppBundle\Enum;

/**
 * TransactionEnum
 */
class TransactionEnum
{
    public static function getMoyenPaiementChoices($withValues = false) {
        $choices = array(
            'smoney' => "S-Money",
            'cheque' => "Chèque",
            'monnaie' => "Monnaie",
            'initial' => "Solde initial",
            'autre' => "Autre"
        );

        if ($withValues) {
            return $choices;
        }

        return array_keys($choices);
    }
}
