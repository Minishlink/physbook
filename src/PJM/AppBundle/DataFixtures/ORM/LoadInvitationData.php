<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\AppBundle\Entity\Event\Invitation;

class LoadInvitationData extends BaseFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $users = array('ancien', 'ancienne', 'p3', 'conscrit');

        $events = array('Nuit des Fignos', 'Bap\'s des 215', 'Apéro Phy\'sbook');

        foreach ($events as $event) {
            foreach ($users as $user) {
                $this->loadInvitation(
                    $manager,
                    $event,
                    $user
                );
            }
        }

        $manager->flush();
    }

    private function loadInvitation(ObjectManager $manager, $event, $invite)
    {
        $invitation = new Invitation();
        $invitation->setEvent($this->getEvenement($event));
        $invitation->setInvite($this->getUser($invite));

        $presence = rand(0, 3) ? rand(0, 1) : null;
        $invitation->setEstPresent($presence);

        $manager->persist($invitation);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
