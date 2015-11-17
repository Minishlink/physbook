<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\AppBundle\Entity\Notifications\Notification;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Enum\Notifications\NotificationEnum;

class LoadNotificationsData extends BaseFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $notificationEnum = NotificationEnum::$list;
        $keys = array_keys($notificationEnum);

        // on code les infos à l'arrache même si certaines vont pas être utilisées
        $infos = array(
            'event' => array(
                'date' => '',
                'heure' => '00:40',
                'prix' => '26.68',
                'event' => 'Évènement 106',
            ),
            'bank' => array(
                'boquette' => 'Phy\'sbook',
                'montant' => '95.157',
                'prix' => '27.149',
                'user' => 'Gorg\'s',
                'item' => 'Pingou',
            ),
            'actus' => array(
                'titre' => 'L\'homme moderne face à un défi majeur : les tâches ménagères',
                'auteur' => 'Ilton²',
            ),
            'consos' => array(
                'quantite' => '1.5',
                'item' => 'Baguette de pain',
                'path_params' => array('slug' => 'brags'),
            ),
        );

        $users = array('ancien', 'p3');

        foreach ($keys as $key) {
            $type = $notificationEnum[$key]['type'];
            $this->loadNotification($manager, $key, $infos[$type], $users, rand(0,1));
        }

        $manager->flush();
    }

    private function loadNotification(ObjectManager $manager, $key, $infos, $users, $received = true)
    {
        if (!is_array($users)) {
            $users = array($users);
        }

        $date = $received ? $this->getRandomDateAgo(5, 30) : $this->getRandomDateAgo(0, 4);

        if (array_key_exists('date', $infos)) {
            $infos['date'] = $date->format('d/m/Y à H:i');
        }

        foreach ($users as $user) {
            /** @var User $user */
            $user = $this->getReference($user."-user");

            $notification = new Notification();
            $notification->setKey($key);
            $notification->setInfos($infos);
            $notification->setUser($user);
            $notification->setReceived($received);
            $notification->setDate($date);

            $manager->persist($notification);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
