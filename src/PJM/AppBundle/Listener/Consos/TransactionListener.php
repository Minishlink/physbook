<?php

namespace PJM\AppBundle\Listener\Consos;

use Doctrine\ORM\Event\LifecycleEventArgs;
use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Entity\Compte;

class TransactionListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Transaction) {
            $transaction = $entity;
            if ($transaction->getStatus() == "OK") {
                $repository = $em->getRepository('PJMAppBundle:Compte');
                $compte = $repository->findOneByUserAndBoquette($transaction->getUser(), $transaction->getBoquette());

                if (!isset($compte)) {
                    $compte = new Compte($transaction->getUser(), $transaction->getBoquette());
                }

                $compte->crediter($transaction->getMontant());
                $em->persist($compte);
            }
        }
    }
}
