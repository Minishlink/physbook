<?php

namespace PJM\AppBundle\Listener\Consos;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Entity\Compte;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class TransactionListener implements EventSubscriber
{
    protected $container;
    protected $comptes;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->comptes = [];
    }

    public function getSubscribedEvents()
    {
        return array(
            'preUpdate',
            'prePersist',
            'onFlush',
        );
    }

    public function prePersist(LifecycleEventArgs $args) {
        $this->persistOrUpdate($args);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->persistOrUpdate($args);
    }

    private function persistOrUpdate($args)
    {
        $transaction = $args->getEntity();
        $logger = $this->container->get('logger');

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

                $logger->info('persistOrUpdate - compte solde après: '.$compte->getSolde());

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
                        $transaction->setStatus($status);
                    }
                }

                $logger->info('persistOrUpdate - fin');

                $this->comptes[] = $compte;

                $logger->info('persistOrUpdate - comptes'.implode($this->comptes));
            }
        }
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $logger = $this->container->get('logger');
        $logger->info('OnFlush - comptes'.implode($this->comptes));

        if(!empty($this->comptes)) {
            $em = $args->getEntityManager();

            foreach ($this->comptes as $compte) {
                $em->persist($compte);
            }

            $this->comptes = [];
            $em->flush();
        }
    }
}
