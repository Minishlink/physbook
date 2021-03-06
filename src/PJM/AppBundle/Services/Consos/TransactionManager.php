<?php

namespace PJM\AppBundle\Services\Consos;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Consos\Transfert;
use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Services\NotificationManager;
use PJM\AppBundle\Services\Rezal;

class TransactionManager
{
    private $em;
    private $notification;
    private $rezal;
    private $transfertManager;

    public function __construct(EntityManager $em, NotificationManager $notification, Rezal $rezal, TransfertManager $transfertManager)
    {
        $this->em = $em;
        $this->notification = $notification;
        $this->rezal = $rezal;
        $this->transfertManager = $transfertManager;
    }

    /**
     * @param Transaction $transaction
     * @param bool $notifyFlash If true, a flash notification will also be sent
     *
     * @return Transfert|Transaction
     */
    public function traiter(Transaction $transaction, $notifyFlash = false)
    {
        if ($transaction->getStatus() == 'OK') {
            // si la transaction est bonne
            // on met à jour le solde du compte associé sur la base Phy'sbook
            $transaction->finaliser();

            // si la transaction concerne la BDD R&z@l
            if (in_array(
                $transaction->getCompte()->getBoquette()->getSlug(),
                array(
                    'cvis',
                    'pians',
                )
            )) {
                // on met à jour le solde du compte associé sur la base R&z@l
                if ($transaction->getMoyenPaiement() != 'operation') {
                    $status = $this->rezal->crediteSolde(
                        $transaction->getCompte()->getUser(),
                        $transaction->getMontant()
                    );
                } else {
                    $status = $this->rezal->debiteSolde(
                        $transaction->getCompte()->getUser(),
                        -$transaction->getMontant()
                    );
                }

                // si une erreur survient
                if ($status !== true) {
                    if ($status === false) {
                        $status = 'REZAL_LIAISON_TRANSACTION';
                    }
                    // on annule la transaction
                    $transaction->finaliser($status);
                }
            }

            // s'il n'y a pas eu d'erreur avant
            if ($transaction->getStatus() == 'OK') {
                // si on fait un crédit pour quelqu'un d'autre
                if (null !== $transaction->getCompteLie()) {
                    // on effectue le transfert vers compteLie
                    $transfert = new Transfert($transaction);
                    $this->transfertManager->traiter($transfert, false, $notifyFlash);
                }
            }
        }

        $this->persist($transaction, true);

        if ($transaction->getStatus() == 'OK') {
            // on notifie que si la transaction a été réalisée
            $this->notification->send('bank.money.transaction.success', array(
                'boquette' => $transaction->getCompte()->getBoquette()->getNom(),
                'montant' => $transaction->showMontant(),
            ), $transaction->getCompte()->getUser());

            // si aussi notification flash
            if ($notifyFlash) {
                $this->notification->sendFlash('success',
                    'La transaction a été enregistrée et le compte '.$transaction->getCompte()->getBoquette()->getNomCourt().
                    ' de '.$transaction->getCompte()->getUser().' a été '.
                    ($transaction->getMoyenPaiement() != 'operation' ? 'crédité' : 'débité').
                    '.'
                );
            }
        } else {
            // on notifie qu'il y a eu une erreur
            $notificationKey = 'bank.money.transaction.fail.';
            $notificationKey .= ((substr($transaction->getStatus(), 0, 5) == 'REZAL') ? 'rezal' : 'default');
            $this->notification->send($notificationKey, array(
                'boquette' => $transaction->getCompte()->getBoquette()->getNom(),
                'montant' => $transaction->showMontant(),
                'erreur' => $transaction->getStatus(),
            ), $transaction->getCompte()->getUser());

            // si aussi notification flash
            if ($notifyFlash) {
                $this->notification->sendFlash('danger',
                    'La transaction a échouée et le compte '.$transaction->getCompte()->getBoquette()->getNomCourt().
                    ' de '.$transaction->getCompte()->getUser().' n\'a pas été modifié pour la raison suivante : '.
                    $transaction->getStatus().'.'
                );
            }
        }

        return isset($transfert) ? $transfert : $transaction;
    }

    public function create($compte, $montant, $moyenPaiement)
    {
        $transaction = new Transaction();
        $transaction->setCompte($compte);
        $transaction->setMontant($montant);
        $transaction->setMoyenPaiement($moyenPaiement);

        return $transaction;
    }

    public function persist(Transaction $transaction, $flush = false)
    {
        $this->em->persist($transaction);

        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * @param $id
     *
     * @return null|Transaction
     */
    public function getById($id)
    {
        return $this->em->getRepository('PJMAppBundle:Transaction')->find($id);
    }
}
