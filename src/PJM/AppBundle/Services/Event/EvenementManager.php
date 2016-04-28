<?php

namespace PJM\AppBundle\Services\Event;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Event\Evenement;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Services\Consos\HistoriqueManager;
use PJM\AppBundle\Services\Consos\TransactionManager;
use PJM\AppBundle\Services\NotificationManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class EvenementManager
{
    private $em;
    private $notification;
    private $authChecker;
    private $router;
    private $historiqueManager;
    private $transactionManager;

    public function __construct(EntityManager $em, AuthorizationChecker $authChecker, Router $router,
        NotificationManager $notification, HistoriqueManager $historiqueManager, TransactionManager $transactionManager)
    {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->router = $router;
        $this->notification = $notification;
        $this->historiqueManager = $historiqueManager;
        $this->transactionManager = $transactionManager;
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

    public function update(Evenement $event, Evenement $oldEvent)
    {
        if (!$event->isMajeur() && $oldEvent->isMajeur() && !$this->authChecker->isGranted('ROLE_ASSO_TRESORS')) {
            $this->notification->sendFlash(
                'danger',
                'Seuls les Harpag\'s Asso peuvent changer un évènement majeur en un évènement mineur.'
            );

            return;
        }

        if ($event->getPrix() != $oldEvent->getPrix() && $event->isPaid()) {
            $this->notification->sendFlash(
                'danger',
                'L\'évènement a déjà été payé : tu ne peux plus changer son prix.'
            );

            return;
        }

        $this->persist($event);

        $this->notification->sendFlash(
            'success',
            'L\'évènement '.$event->getNom().' a été modifié.'
        );

        // envoyer notifications aux invités
        $invites = array_merge($event->getInvites(true), $event->getInvites(null));

        if ($event->getDateDebut() != $oldEvent->getDateDebut()) {
            $this->notification->send('event.changement.date', array(
                'event' => $event->getNom(),
                'date' => $event->getDateDebut()->format('d/m/Y à H:i'),
            ), $invites);
        }

        if ($event->getPrix() != $oldEvent->getPrix()) {
            $this->notification->send('event.changement.prix', array(
                'event' => $event->getNom(),
                'prix' => $event->showPrix(),
            ), $invites);
        }
    }

    public function remove(Evenement $event)
    {
        $inscrits = $event->getInvites(true);

        $this->em->remove($event);

        $this->notification->sendFlash(
            'success',
            'L\'évènement '.$event->getNom().' a été supprimé.'
        );

        if (new \DateTime() < $event->getDateFin()) {
            // envoyer notifications aux inscrits
            $this->notification->send('event.suppression', array(
                'event' => $event->getNom(),
            ), $inscrits, false);
        }
        
        $this->em->flush();
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
            'event' => $event,
        );
    }

    /**
     * Get the events between these dates.
     * If the user is specified, then informations that he should not see will be deleted: in this case,
     * you should NEVER persist these events!
     *
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param User $user
     * @param Boquette $boquette If specified, events will be filtered by this Boquette
     * @param bool $forFullCalendar If true, return an array compatible with FullCalendar
     *
     * @return array|\PJM\AppBundle\Entity\Event\Evenement[]
     */
    public function getBetweenDates(\DateTime $dateDebut, \DateTime $dateFin, User $user = null, Boquette $boquette = null, $forFullCalendar = false)
    {
        // trouver tous les évènements correspondants
        $events = $this->em->getRepository('PJMAppBundle:Event\Evenement')->findBetweenDates($dateDebut, $dateFin, $boquette);

        // si c'est pour être utilisé ensuite comme source de FullCalendar
        if ($forFullCalendar) {
            $events = array_map(function (Evenement $event) use ($user) {
                // on filtre si l'utilisateur est spécifié
                if (isset($user)) {
                    if (!$event->canBeSeenByUser($user)) {
                        $event->setNom('Évènement privé');
                        $event->setSlug('');
                        $event->setDescription('');
                        $event->setLieu('');
                    }
                }

                return array(
                    'title' => $event->getNom(),
                    'allDay' => $event->isDay(),
                    'start' => $event->getDateDebut()->format('c'),
                    'end' => $event->getDateFin()->format('c'),
                    'url' => $this->router->generate('pjm_app_event_index', array('slug' => $event->getSlug()), UrlGeneratorInterface::ABSOLUTE_URL),
                    'className' => !$event->isPublic() ? 'prive' : ($event->isMajeur() ? 'majeur' : 'mineur'),
                    'lieu' => $event->getLieu(),
                );
            }, $events);
        }

        return $events;
    }

    public function canEdit(User $user, Evenement $event)
    {
        return $event->getCreateur() === $user
            || $this->authChecker->isGranted('ROLE_ADMIN');
    }

    public function canUserTriggerPayment(User $user, Evenement $event)
    {
        return $this->authChecker->isGranted('ROLE_ADMIN')
            || ($event->isMajeur() && $this->authChecker->isGranted('ROLE_ZIPIANS_HARPAGS'))
            || (!$event->isMajeur() && $event->getCreateur() == $user);
    }

    public function paiement(Evenement $event)
    {
        $inscrits = $event->getParticipants();

        if (empty($inscrits) || $event->isPaid()) {
            return false;
        }

        // on crée l'item associé
        $item = new Item();
        $item->setLibelle('Évènement '.$event->getNom().' ('.$event->getDateDebut()->format('d/m').')');
        $item->setPrix($event->getPrix());
        $item->setImage($event->getImage());
        $item->setSlug('event_'.$event->getSlug().'_'.$event->getDateDebut()->format('YmdHis'));
        $item->setInfos(array('event'));
        $item->setValid(true);
        $item->setBoquette($this->em->getRepository('PJMAppBundle:Boquette')->findOneBySlug('pians')); // bucquage sur compte Pi
        $item->setUsersHM(null);
        $event->setItem($item);
        $this->em->persist($event);

        // on fait payer chaque inscrit
        $success = 0;
        foreach ($inscrits as $inscrit) {
            $success += $this->historiqueManager->paiement($inscrit, $item, false, false);
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

        if (!$event->isMajeur()) {
            // on crédite le créateur
            $compteCreateur = $this->em->getRepository('PJMAppBundle:Compte')->findOneByUserAndBoquetteSlug($event->getCreateur(), 'pians');
            $transaction = $this->transactionManager->create($compteCreateur, $event->getPrix() * count($inscrits), 'event');
            $transaction->setStatus('OK');
            $transaction->setInfos($event->getNom().' ('.$event->getDateDebut()->format('d/m').')');
            $this->transactionManager->traiter($transaction);
        }

        $this->em->flush();

        $this->notification->sendFlash(
            'success',
            'L\'évènement '.$event->getNom().' a été débité sur les comptes des inscrits'.($event->isMajeur() ? '' : ' et crédité sur le compte de l\'organisateur').'.'
        );

        return true;
    }

    public function checkPassageMajeur(Evenement $event, $limit)
    {
        if (!$event->isMajeur()) {
            if ($this->em->getRepository('PJMAppBundle:Event\Invitation')->countParticipations($event) > $limit) {
                $event->setMajeur(true);
                $this->persist($event);
            }
        }
    }

    /**
     * Notify users of incoming events (24h).
     *
     * This is called by NotificationsCommand every day with a cron task.
     */
    public function notifyForNextEvents()
    {
        $nextEvents = $this->em->getRepository('PJMAppBundle:Event\Evenement')->getNextEvents();

        /** @var Evenement $event */
        foreach ($nextEvents as $event) {
            $participants = $event->getParticipants();

            if (!empty($participants)) {
                $this->notification->send('event.incoming', array(
                    'event' => $event->getNom(),
                    'heure' => $event->getDateDebut()->format('H\hi'),
                ), $participants);
            }
        }
    }
}
