<?php

namespace PJM\AppBundle\Listener\Consos;

use Doctrine\ORM\Event\LifecycleEventArgs;
use PJM\AppBundle\Entity\Historique;
use PJM\AppBundle\Entity\Compte;

class HistoriqueListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Historique) {
            $historique = $entity;
            if ($historique->getValid()) {
                $repository = $em->getRepository('PJMAppBundle:Compte');
                $compte = $repository->findOneByUserAndBoquette($historique->getUser(), $historique->getItem()->getBoquette());

                if (!isset($compte)) {
                    $compte = new Compte($historique->getUser(), $historique->getItem()->getBoquette());
                }

                $compte->debiter($historique->getPrix());
                $em->persist($compte);
            }
        }
    }
}
