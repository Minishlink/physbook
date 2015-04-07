<?php

namespace PJM\AppBundle\Listener\Consos;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PJM\AppBundle\Entity\Historique;
use PJM\AppBundle\Entity\Compte;

class HistoriqueListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preRemove',
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Historique) {
            $historique = $entity;
            if ($historique->getItem()->getBoquette()->getSlug() != 'cvis' && $historique->getItem()->getBoquette()->getSlug() != 'pians') {
                if ($historique->getValid()) {
                    $repository = $em->getRepository('PJMAppBundle:Compte');
                    $compte = $repository->findOneByUserAndBoquetteSlug($historique->getUser(), $historique->getItem()->getBoquette()->getSlug());

                    if (!isset($compte)) {
                        $compte = new Compte($historique->getUser(), $historique->getItem()->getBoquette());
                    }

                    $compte->debiter($historique->getPrix());
                    $em->persist($compte);
                }
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Historique) {
            $historique = $entity;
            if ($historique->getItem()->getBoquette()->getSlug() != 'cvis' && $historique->getItem()->getBoquette()->getSlug() != 'pians') {
                if ($historique->getValid()) {
                    $repository = $em->getRepository('PJMAppBundle:Compte');
                    $compte = $repository->findOneByUserAndBoquetteSlug($historique->getUser(), $historique->getItem()->getBoquette()->getSlug());

                    if (!isset($compte)) {
                        $compte = new Compte($historique->getUser(), $historique->getItem()->getBoquette());
                    }

                    $compte->crediter($historique->getPrix());
                    $em->persist($compte);
                }
            }
        }
    }
}
