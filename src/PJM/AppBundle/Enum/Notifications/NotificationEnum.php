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
        'bank.money.buy.item' => array(
            'titre' => 'Achat d\'un item',
            'type' => 'bank',
            'path' => 'pjm_app_banque_index',
            'infos' => array('item', 'prix')
        ),
        'bank.money.negats' => array(
            'titre' => 'Alerte de négat\'s',
            'type' => 'bank',
            'path' => 'pjm_app_banque_index',
            'infos' => array('boquette', 'montant')
        ),
    );

    public function getKeys() {
        return array_keys($this::$list);
    }
}
