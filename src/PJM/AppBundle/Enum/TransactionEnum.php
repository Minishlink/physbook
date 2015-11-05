<?php

namespace PJM\AppBundle\Enum;

/**
 * TransactionEnum.
 */
class TransactionEnum
{
    public static function getMoyenPaiementChoices($withValues = false)
    {
        $choices = array(
            'smoney' => 'S-Money',
            'lydia' => 'Lydia',
            'cheque' => 'Chèque',
            'monnaie' => 'Monnaie',
            'initial' => 'Solde initial',
            'operation' => 'Opération',
            'autre' => 'Autre',
            'event' => 'Évènement',
        );

        if ($withValues) {
            return $choices;
        }

        return array_keys($choices);
    }
}
