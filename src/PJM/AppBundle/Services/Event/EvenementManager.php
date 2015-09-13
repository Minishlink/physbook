<?php

namespace PJM\AppBundle\Services\Event;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Event\Evenement;
use PJM\AppBundle\Entity\Event\Invitation;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Services\Notification;

class EvenementManager
{
    private $em;
    private $notification;

    public function __construct(EntityManager $em, Notification $notification)
    {
        $this->em = $em;
        $this->notification = $notification;
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
        $this->persist($event); // needed for slug generation

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

        $this->notification->sendFlash(
            'success',
            "L'évènement a été créé."
        );
    }

    public function persist(Evenement $event)
    {
        $this->em->persist($event);
        $this->em->flush();
    }

    public function update(Evenement $event)
    {
        $this->persist($event);

        $this->notification->sendFlash(
            'success',
            "L'évènement a été modifié."
        );

        // envoyer notifications aux invités
    }

    public function remove(Evenement $event)
    {
        $this->em->remove($event);
        $this->em->flush();

        $this->notification->sendFlash(
            'success',
            "L'évènement a été supprimé."
        );

        // envoyer notifications aux inscrits
    }

    public function get(Evenement $event = null, User $user, $nombreMax)
    {
        $repo = $this->em->getRepository('PJMAppBundle:Event\Evenement');

        // si c'est l'accueil des évènements
        if ($event === null) {
            // on va chercher les $nombreMax-1 premiers events à partir de ce moment
            $listeEvents = $repo->getEvents($user, $nombreMax - 1);

            // on définit l'event en cours comme celui le plus proche de la date
            if (count($listeEvents) > 0) {
                $event = $listeEvents[0];
            }
        } else {
            // on va chercher les $nombreMax-2 évènements après cet event
            $listeEvents = $repo->getEvents($user, $nombreMax - 2, 'after', $event->getDateDebut());

            $listeEvents = array_merge(array($event), $listeEvents);
        }

        $dateRechercheAvant = ($event !== null) ? $event->getDateDebut() : new \DateTime();
        // on va chercher les events manquants avant
        $eventsARajouter = $repo->getEvents($user, $nombreMax - count($listeEvents), 'before', $dateRechercheAvant, $event);

        $listeEvents = array_merge($eventsARajouter, $listeEvents);

        // si on n'avait toujouts pas d'évènement
        if ($event === null && count($listeEvents) > 0) {
            $event = end($listeEvents);
        }

        return array(
            'listeEvents' => $listeEvents,
            'event' => $event
        );
    }
}
