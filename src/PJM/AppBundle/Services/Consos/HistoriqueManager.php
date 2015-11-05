<?php

namespace PJM\AppBundle\Services\Consos;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Historique;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Services\NotificationManager;
use PJM\AppBundle\Services\Rezal;

class HistoriqueManager
{
    private $em;
    private $notification;
    private $rezal;

    public function __construct(EntityManager $em, NotificationManager $notification, Rezal $rezal)
    {
        $this->em = $em;
        $this->notification = $notification;
        $this->rezal = $rezal;
    }

    /**
     * @param User $user
     * @param Item $item
     * @param bool $verifyEnoughMoney
     * @param bool $flush
     *
     * @return bool
     */
    public function paiement(User $user, Item $item, $verifyEnoughMoney = false, $flush = true)
    {
        if ($verifyEnoughMoney &&
            null === $this->em->getRepository('PJMAppBundle:Compte')->findOneByUserAndBoquetteAndMinSolde($user, $item->getBoquette(), $item->getPrix())) {
            $this->notification->sendFlash(
                'danger',
                'Tu n\'as pas assez d\'argent sur ton compte '.$item->getBoquette()->getNom().' pour acheter '.$item->getLibelle().'.'
            );

            return false;
        }

        $historique = new Historique();
        $historique->setItem($item);
        $historique->setUser($user);
        $historique->setValid(null);

        // si la boquette concernée débite sur le R&zal
        if (in_array($item->getBoquette()->getSlug(), array('pians', 'cvis'))) {
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

                // notify ZiPhy'sbook
                $this->notification->sendToEmail(
                    'zi@physbook.fr',
                    'Il y a eu une erreur R&z@l lors de l\'achat de '.$item->getLibelle().' ('.$item->showPrix().'€) à la date du '.$historique->getDate()->format('d/m/Y H:i:s').' par '.$user.'.'
                );

                return false;
            }

            // si pas d'erreur coté R&z@l, débiter compte
        }

        $historique->setValid(true);
        $this->em->persist($historique);

        if ($flush) {
            $this->em->flush();
        }

        $this->notification->send('bank.money.achat', array(
            'item' => $item->getLibelle(),
            'prix' => $item->showPrix(),
        ), $user, $flush);

        if (!$verifyEnoughMoney) {
            $compte = $this->em->getRepository('PJMAppBundle:Compte')->findOneByUserAndBoquetteSlug($user, $item->getBoquette()->getSlug());
            if ($compte->getSolde() < 0) {
                $this->notification->send('bank.money.negats', array(
                    'boquette' => $item->getBoquette()->getNom(),
                    'montant' => -$compte->getSolde() / 100,
                ), $user, $flush);
            }
        }

        return true;
    }
}
