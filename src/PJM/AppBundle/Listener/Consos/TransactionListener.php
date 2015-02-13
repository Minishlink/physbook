<?php

namespace PJM\AppBundle\Listener\Consos;

use Doctrine\ORM\Event\LifecycleEventArgs;
use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Entity\Compte;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class TransactionListener
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $transaction = $args->getEntity();

        if ($transaction instanceof Transaction) {
            if ($transaction->getStatus() == "OK") {
                // si la transaction est bonne
                // on met à jour le solde du compte associé sur la base Phy'sbook
                $em = $args->getEntityManager();
                $repository = $em->getRepository('PJMAppBundle:Compte');
                $compte = $repository->findOneByUserAndBoquette($transaction->getUser(), $transaction->getBoquette());

                if (!isset($compte)) {
                    $compte = new Compte($transaction->getUser(), $transaction->getBoquette());
                }

                $compte->crediter($transaction->getMontant());
                $em->persist($compte);

                // si la transaction concerne la BDD R&z@l
                if (in_array(
                    $transaction->getBoquette()->getSlug(), array(
                        'cvis',
                        'pians'
                    )
                )) {
                    // on met à jour le solde du compte associé sur la base R&z@l
                    // TODO vérifier qu'on est pas en mode synchro avec la base R&z@l
                    // TODO prendre username car fam'ss modifiable, donc problème de doublon de transaction lors de la synchro avec la base Rezal...
                    // TODO enregistrement dans l'historique
                    $rezal = $this->container->get('pjm.services.rezal');
                    $status = $rezal->crediteSolde(
                        $transaction->getUser()->getFams(),
                        $transaction->getUser()->getTabagns(),
                        $transaction->getUser()->getProms(),
                        $transaction->getMontant(),
                        $transaction->getDate()->format('Y-m-d H:i:s')
                    );

                    // si une erreur survient
                    if ($status !== true) {
                        if ($status === false) {
                            $status = 'REZAL_LIAISON_TRANSACTION';
                        }
                        // on annule la transaction
                        $compte->debiter($transaction->getMontant());
                        $em->persist($compte);
                        $transaction->setStatus($status);
                    }
                }
            }
        }
    }
}
