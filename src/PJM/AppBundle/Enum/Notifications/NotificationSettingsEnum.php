<?php

namespace PJM\AppBundle\Enum\Notifications;

/**
 * NotificationSettingsEnum.
 */
class NotificationSettingsEnum
{
    public static function getSubscriptionsChoices($withValues = false)
    {
        $choices = array(
            'bank' => 'Banque',
            'actus' => 'Actualités',
            'event' => 'Évènements',
            'message' => 'Messages',
        );

        if ($withValues) {
            return $choices;
        }

        return array_keys($choices);
    }

    public static function getDefaultSubscriptionsChoices()
    {
        return array(
            'bank',
            'actus',
            'event',
        );
    }
}
