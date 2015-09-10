<?php

namespace PJM\AppBundle\Services\Event;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Event\Evenement;
use PJM\AppBundle\Entity\Event\Invitation;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\User;

class EvenementManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(User $createur)
    {
        $event = new Evenement();
        $event->setCreateur($createur);

        return $event;
    }

    public function configure(Evenement $event)
    {
        $invitation = new Invitation();
        $invitation->setEvent($event);
        $invitation->setInvite($event->getCreateur());
        $invitation->setEstPresent(true);
        $this->em->persist($invitation);
        $this->persist($event);

        if ($event->getPrix() > 0) {
            $item = new Item();
            $item->setLibelle($event->getNom());
            $item->setPrix($event->getPrix());
            $item->setImage($event->getImage());
            $item->setSlug("event_".$event->getSlug());
            $item->setDate($event->getDateCreation());
            $item->setInfos(array('event'));
            $item->setValid(true);
            // bucquage sur compte Pi
            $item->setBoquette($this->em->getRepository('PJMAppBundle:Boquette')->findOneBySlug('pians'));
            $item->setUsersHM(null);
            $event->setItem($item);
            $this->persist($event);
        }
    }

    public function persist(Evenement $event)
    {
        $this->em->persist($event);
        $this->em->flush();
    }
}
