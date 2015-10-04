<?php

namespace PJM\AppBundle\Enum\Notifications;

class NotificationEnum
{
    /*
     * Sous la forme :
     * key = clé d'identification sans le préfixe "notification.content" dans le fichier des traductions
     *    type = correspond au type de notifications dont la liste possible est dans ReglagesNotificationsEnum.php
     *    path = correspond à la route menant à l'URL lorsque l'on clique sur la notification
     *    infos = correspond aux variables utilisées dans le message situé dans le fichier des traductions
     */
    public static $list = array(
        'bank.money.buy.item' => array(
            'type' => 'bank',
            'path' => 'pjm_app_banque_index',
            'infos' => array('item', 'prix')
        ),
    );

    public function getKeys() {
        return array_keys($this::$list);
    }
}
