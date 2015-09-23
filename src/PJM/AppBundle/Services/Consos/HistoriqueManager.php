<?php

namespace PJM\AppBundle\Services\Consos;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Historique;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Services\Notification;
use PJM\AppBundle\Services\Rezal;

class HistoriqueManager
{
    private $em;
    private $notification;
    private $rezal;

    public function __construct(EntityManager $em, Notification $notification, Rezal $rezal)
    {
        $this->em = $em;
        $this->notification = $notification;
        $this->rezal = $rezal;
    }

    /**
     * @param User $user
     * @param Item $item
     * @param bool $flush
     * @return bool
     */
    public function paiement(User $user, Item $item, $flush = true)
    {
        $historique = new Historique();
        $historique->setItem($item);
        $historique->setUser($user);
        $historique->setValid(null);

        // on met à jour le solde du compte associé sur la base R&z@l
        $status = $this->rezal->debiteSolde(
            $historique->getUser(),
            $historique->getPrix()
        );

        // si une erreur survient
        if ($status !== true) {
            $historique->setValid(false);
            $this->em->persist($historique);

            if ($flush) {
                $this->em->flush();
            }

            // TODO notification échec user + harpag's

            return false;
        }

        // si pas d'erreur coté R&z@l, débiter compte
        $historique->setValid(true);
        $this->em->persist($historique);

        if ($flush) {
            $this->em->flush();
        }

        // TODO notification achat user (+ alerte négats)

        return true;
    }
}
