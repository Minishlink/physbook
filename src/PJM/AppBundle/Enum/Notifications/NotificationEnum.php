<?php

namespace PJM\AppBundle\Enum\Notifications;

class NotificationEnum
{
    /*
     * Sous la forme :
     * key = clé d'identification sans le préfixe "notification.content" dans le fichier des traductions
     *    titre = titre de la notification, pourra être utilisé dans le futur pour des réglages plus fins des abonnements
     *    type = correspond au type de notifications dont la liste possible est dans NotificationSettingsEnum.php
     *    path = correspond à la route menant à l'URL lorsque l'on clique sur la notification
     *    infos = correspond aux variables utilisées dans le message situé dans le fichier des traductions
     */
    public static $list = array(
        'bank.money.achat' => array(
            'titre' => 'Achat d\'un item',
            'type' => 'bank',
            'path' => 'pjm_app_banque_index',
            'infos' => array('item', 'prix'),
        ),
        'bank.money.negats' => array(
            'titre' => 'Alerte de négat\'s',
            'type' => 'bank',
            'path' => 'pjm_app_banque_index',
            'infos' => array('boquette', 'montant'),
        ),
        'bank.money.transaction' => array(
            'titre' => 'Transaction effectuée',
            'type' => 'bank',
            'path' => 'pjm_app_banque_index',
            'infos' => array('boquette', 'montant'),
        ),
        'bank.money.transfert.reception' => array(
            'titre' => 'Transfert reçu',
            'type' => 'bank',
            'path' => 'pjm_app_banque_index',
            'infos' => array('boquette', 'montant', 'user'),
        ),
        'bank.money.transfert.envoi' => array(
            'titre' => 'Transfert envoyé',
            'type' => 'bank',
            'path' => 'pjm_app_banque_index',
            'infos' => array('boquette', 'montant', 'user'),
        ),
        'event.changement.prix' => array(
            'titre' => 'Changement de prix d\'un évènement',
            'type' => 'event',
            'path' => 'pjm_app_event_index',
            'infos' => array('event', 'prix'),
        ),
        'event.changement.date' => array(
            'titre' => 'Changement de date d\'un évènement',
            'type' => 'event',
            'path' => 'pjm_app_event_index',
            'infos' => array('event', 'date'),
        ),
        'event.suppression' => array(
            'titre' => 'Suppression d\'un évènement',
            'type' => 'event',
            'path' => 'pjm_app_event_index',
            'infos' => array('event'),
        ),
        'event.invitation' => array(
            'titre' => 'Invitation à un évènement',
            'type' => 'event',
            'path' => 'pjm_app_event_index',
            'infos' => array('event', 'date'),
        ),
        'event.incoming' => array(
            'titre' => 'Evènement à venir',
            'type' => 'event',
            'path' => 'pjm_app_event_index',
            'infos' => array('event', 'heure'),
        ),
    );

    public function getKeys()
    {
        return array_keys($this::$list);
    }
}
