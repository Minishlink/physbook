<?php

namespace PJM\AppBundle\Services\Event;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Event\Evenement;
use PJM\AppBundle\Entity\Event\Invitation;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Services\Notification;

class InvitationManager
{
    private $em;
    private $notification;

    public function __construct(EntityManager $em, Notification $notification)
    {
        $this->em = $em;
        $this->notification = $notification;
    }

    /**
     * Returns the invitation to an event for a specific user.
     *
     * @param User $user
     * @param Evenement $event
     * @return Invitation
     */
    public function getInvitationFromUserToEvent(User $user, Evenement $event)
    {
        return $this->em->getRepository('PJMAppBundle:Event\Invitation')
            ->findOneBy(array('invite' => $user, 'event' => $event));
    }

    /**
     * @param Invitation $invitation
     * @param User $user
     * @param Evenement $event
     * @return Invitation
     */
    public function toggleInscriptionFromUserToEvent(Invitation $invitation, User $user, Evenement $event)
    {
        if ($invitation !== null) {
            // si on est déjà un invité
            // on annule si on participait et si c'est payant on rembourse si la deadline de paiement n'est pas passée (sinon on dit à l'utilisateur de s'arranger avec l'organisateur)
            // on s'inscrit si on était juste invité et on paye si c'est payant
            $invitation->setEstPresent(null === $invitation->getEstPresent() || !$invitation->getEstPresent());

            $this->em->persist($invitation);
            $this->em->flush();

            $this->notification->sendFlash(
                'success',
                'Ta modification a bien été prise en compte.'
            );
        } else {
            // sinon on vérifie que l'on peut accéder à cet évènement
            if ($event->isPublic()) {
                //on crée une nouvelle invitation
                $invitation = new Invitation();
                $invitation->setEvent($event);
                $invitation->setInvite($user);
                $invitation->setEstPresent(true);

                // on paye si besoin

                $this->em->persist($invitation);
                $this->em->flush();

                $this->notification->sendFlash(
                    'success',
                    'Tu participes bien à cet évènement.'
                );
            } else {
                $this->notification->sendFlash(
                    'warning',
                    "Tu n'as pas accès à cet évènement."
                );
            }
        }

        return $invitation;
    }

    public function sendInvitations($users, Evenement $event) {
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
                    $this->notification->sendPushToUser($user, 'Invitation à un évènement', 'events');
                }
            }
        }

        $this->em->flush();

        $this->notification->sendFlash(
            'success',
            'Tes invitations ont bien été envoyées.'
        );
    }
}
