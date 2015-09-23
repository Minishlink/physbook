<?php

namespace PJM\AppBundle\Services\Consos;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Consos\Transfert;
use PJM\AppBundle\Services\Notification;
use PJM\AppBundle\Services\Rezal;

class TransfertManager
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
     * @param Transfert $transfert
     * @return Transfert
     */
    public function traiter(Transfert $transfert)
    {
        // TODO notification destinataire
        // on met à jour le solde des comptes associés sur la base Phy'sbook
        $transfert->finaliser();

        // si le transfert concerne la BDD R&z@l
        if (in_array(
            $transfert->getReceveur()->getBoquette()->getSlug(), array(
                'cvis',
                'pians',
            )
        )) {
            // on met à jour le solde des comptes associés sur la base R&z@l
            $status = $this->rezal->debiteSolde(
                $transfert->getEmetteur()->getUser(),
                $transfert->getMontant()
            );

            // si une erreur survient
            if ($status !== true) {
                if ($status === false) {
                    $status = 'REZAL_LIAISON_TRANSACTION';
                }

                // on annule la transaction
                $transfert->finaliser('1. '.$status);
            } else {
                $status = $this->rezal->crediteSolde(
                    $transfert->getReceveur()->getUser(),
                    $transfert->getMontant()
                );

                // si une erreur survient
                if ($status !== true) {
                    if ($status === false) {
                        $status = 'REZAL_LIAISON_TRANSACTION';
                    }
                    // on annule la transaction
                    $transfert->finaliser('2. '.$status);

                    // on recrédite l'émetteur sur le pians
                    $status = $this->rezal->crediteSolde(
                        $transfert->getEmetteur()->getUser(),
                        $transfert->getMontant()
                    );

                    if ($status !== true) {
                        if ($status === false) {
                            $status = 'REZAL_LIAISON_TRANSACTION';
                        }

                        $transfert->setStatus('3. '.$status);
                    }
                }
            }
        }

        return $transfert;
    }
}
