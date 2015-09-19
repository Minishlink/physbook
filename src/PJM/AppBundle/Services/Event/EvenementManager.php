<?php

namespace PJM\AppBundle\Services\Event;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Event\Evenement;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Services\Consos\HistoriqueManager;
use PJM\AppBundle\Services\Notification;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class EvenementManager
{
    private $em;
    private $notification;
    private $authChecker;
    private $historiqueManager;

    public function __construct(EntityManager $em, Notification $notification, AuthorizationChecker $authChecker, HistoriqueManager $historiqueManager)
    {
        $this->em = $em;
        $this->notification = $notification;
        $this->authChecker = $authChecker;
        $this->historiqueManager = $historiqueManager;
    }

    public function create(User $createur)
    {
        $event = new Evenement();
        $event->setCreateur($createur);

        return $event;
    }

    public function configure(Evenement $event)
    {
        $this->persist($event);

        $this->notification->sendFlash(
            'success',
            'L\'évènement '.$event->getNom().' a été créé.'
        );
    }

    public function persist(Evenement $event)
    {
        $this->em->persist($event);
        $this->em->flush();
    }

    public function update(Evenement $event, $isMajeurOriginal)
    {
        if (!$event->isMajeur() && $isMajeurOriginal && !$this->authChecker->isGranted('ROLE_ASSO_TRESORS')) {
            $this->notification->sendFlash(
                'danger',
                'Seuls les Harpag\'s Asso peuvent changer un évènement majeur en un évènement mineur.'
            );
            return;
        }

        // TODO validation changement de prix pas possible après paiement

        $this->persist($event);

        $this->notification->sendFlash(
            'success',
            'L\'évènement '.$event->getNom().' a été modifié.'
        );

        // TODO envoyer notifications aux invités
    }

    public function remove(Evenement $event)
    {
        $this->em->remove($event);
        $this->em->flush();

        $this->notification->sendFlash(
            'success',
            'L\'évènement '.$event->getNom().' a été supprimé.'
        );

        // TODO envoyer notifications aux inscrits
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

    public function canEdit(User $user, Evenement $event)
    {
        return ($event->getCreateur() === $user
            || $this->authChecker->isGranted('ROLE_ADMIN'));
    }

    public function canUserTriggerPayment(User $user, Evenement $event)
    {
        return ($this->authChecker->isGranted('ROLE_ADMIN')
            || ($event->isMajeur() && $this->authChecker->isGranted('ROLE_ZIPIANS_HARPAGS'))
            || (!$event->isMajeur() && $event->getCreateur() == $user));
    }

    public function paiement(Evenement $event)
    {
        $inscrits = $event->getParticipants();

        if (empty($inscrits) || $event->isPaid()) {
            return false;
        }

        // on crée l'item associé
        $item = new Item();
        $item->setLibelle("Évènement ".$event->getNom()." (".$event->getDateDebut()->format("d/m").")");
        $item->setPrix($event->getPrix());
        $item->setImage($event->getImage());
        $item->setSlug("event_".$event->getSlug()."_".$event->getDateDebut()->format("YmdHis"));
        $item->setInfos(array('event'));
        $item->setValid(true);
        $item->setBoquette($this->em->getRepository('PJMAppBundle:Boquette')->findOneBySlug('pians')); // bucquage sur compte Pi
        $item->setUsersHM(null);
        $event->setItem($item);
        $this->em->persist($event);

        // on fait payer chaque inscrit
        $success = 0;
        foreach ($inscrits as $inscrit) {
            $success += $this->historiqueManager->paiement($inscrit, $item, false);
        }

        if (!$success) {
            $this->notification->sendFlash(
                'warning',
                'Le déclenchement du paiement a échoué ! Les opérations de débit ont toutes échouées.
                Cela est problablement dû à une erreur de connexion avec le serveur du R&z@l. Prends contact avec un ZiPhy\'sbook.'
            );

            // on n'enregistre rien en BDD

            return false;
        }

        if (count($inscrits) != $success) {
            $this->notification->sendFlash(
                'danger',
                'L\'évènement '.$event->getNom().' a été débité sur certains comptes seulement ('.$success.'/'.count($inscrits).' personnes)
                car il y a eu des problèmes lors du débit. Certains utilisateurs n\'ont peut-être pas de compte Pi sur le serveur R&z@l.
                L\'organisateur n\'a pas été crédité. Prends contact avec un Harpag\'s ou/et un ZiPhy\'sbook.'
            );

            // on enregistre les transactions effectuées et celles non effectuées (valid=false)
            $this->em->flush();

            return false;
        }

        // TODO on crédite le créateur

        $this->em->flush();

        // TODO envoyer notification débit + alerte négat's

        $this->notification->sendFlash(
            'success',
            'L\'évènement '.$event->getNom().' a été débité sur les comptes des inscrits et crédité sur le compte de l\'organisateur.'
        );

        return true;
    }
}
