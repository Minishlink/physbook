<?php

namespace PJM\AppBundle\Services\Event;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Event\Evenement;
use PJM\AppBundle\Entity\Event\Invitation;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Services\NotificationManager;

class InvitationManager
{
    private $em;
    private $notification;

    public function __construct(EntityManager $em, NotificationManager $notification)
    {
        $this->em = $em;
        $this->notification = $notification;
    }

    /**
     * Returns the invitation to an event for a specific user.
     *
     * @param User      $user
     * @param Evenement $event
     *
     * @return Invitation
     */
    public function getInvitationFromUserToEvent(User $user, Evenement $event)
    {
        return $this->em->getRepository('PJMAppBundle:Event\Invitation')
            ->findOneBy(array('invite' => $user, 'event' => $event));
    }

    /**
     * @param Invitation $invitation
     * @param User       $user
     * @param Evenement  $event
     * @param int        $solde
     *
     * @return Invitation
     */
    public function toggleInscriptionFromUserToEvent(Invitation $invitation = null, User $user, Evenement $event, $solde = null)
    {
        if (new \DateTime('now') > $event->getDateDeadline()) {
            $this->notification->sendFlash(
                'warning',
                'La deadline de l\'évènement '.$event->getNom().' est passée.'
            );

            return;
        }

        if ($invitation !== null) {
            // si on est déjà un invité
            // on annule si on participait et si c'est payant on rembourse si la deadline de paiement n'est pas passée (sinon on dit à l'utilisateur de s'arranger avec l'organisateur)
            // on s'inscrit si on était juste invité et on paye si c'est payant
            $invitation->setEstPresent(null === $invitation->getEstPresent() || !$invitation->getEstPresent());
        } else {
            // sinon on vérifie que l'on peut accéder à cet évènement
            if ($event->isPublic() || $event->getCreateur() == $user) {
                //on crée une nouvelle invitation
                $invitation = new Invitation();
                $invitation->setEvent($event);
                $invitation->setInvite($user);
                $invitation->setEstPresent(true);
            } else {
                $this->notification->sendFlash(
                    'warning',
                    'Tu n\'as pas accès à l\'évènement '.$event->getNom().'.'
                );

                return;
            }
        }

        if ($invitation->getEstPresent()) {
            // si l'utilisateur veut s'inscrire
            if ($event->getMaxParticipants()) {
                // si il y a un nombre de participants maximum
                // on vérifie qu'il reste des places
                if (count($event->getParticipants()) > $event->getMaxParticipants()) {
                    $this->notification->sendFlash(
                        'warning',
                        'Tu ne peux pas participer car il n\'y a plus de places disponibles.'
                    );

                    return;
                }
            }

            if ($event->getPrix() && isset($solde)) {
                // si l'évènement est payant
                // on vérifie qu'il a assez d'argent sur son compte
                $need = $event->getPrix() - $solde;
                if ($need > 0) {
                    $this->notification->sendFlash(
                        'warning',
                        'Tu n\'as pas assez d\'argent sur ton compte. Recharge-le d\'au moins '.($need / 100).'€.'
                    );

                    return;
                }
            }
        }

        $this->em->persist($invitation);
        $this->em->flush();

        if ($invitation->getEstPresent()) {
            $this->notification->sendFlash(
                'success',
                'Tu participes à l\'évènement '.$event->getNom().'.'
            );
        } else {
            $this->notification->sendFlash(
                'success',
                'Tu ne participes pas à l\'évènement '.$event->getNom().'.'
            );
        }

        return $invitation;
    }

    public function sendInvitations($users, Evenement $event)
    {
        $notifUsers = array();

        foreach ($users as $user) {
            // on vérifie que c'est un utilisateur
            if ('PJM\AppBundle\Entity\User' == get_class($user)) {
                // on vérifie qu'il n'est pas déjà invité
                $invitation = $this->getInvitationFromUserToEvent($user, $event);

                if ($invitation === null) {
                    $invitation = new Invitation();
                    $invitation->setEvent($event);
                    $invitation->setInvite($user);
                    $this->em->persist($invitation);

                    // on envoit la notification
                    $notifUsers[] = $user;
                }
            }
        }

        $this->em->flush();

        $this->notification->send('event.invitation', array(
            'event' => $event->getNom(),
            'date' => $event->getDateDebut()->format('d/m/Y à H:i'),
            'path_params' => array('slug' => $event->getSlug()),
        ), $notifUsers);

        $this->notification->sendFlash(
            'success',
            'Tes invitations à l\'évènement '.$event->getNom().' ont été envoyées.'
        );
    }
}
