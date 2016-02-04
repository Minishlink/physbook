<?php

namespace PJM\AppBundle\Services\Boquette;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Entity\Item;

class BragsService extends BoquetteService
{
    private $itemSlug = 'baguette';

    public function __construct(EntityManager $em, $specialBoquettes)
    {
        parent::__construct($em, $specialBoquettes['boulangerie']);
    }

    public function getCurrentBaguette()
    {
        $baguette = $this->getItem($this->itemSlug);

        if (null === $baguette) {
            $baguette = new Item();
            $baguette->setLibelle('Baguette de pain');
            $baguette->setPrix(65);
            $baguette->setSlug($this->itemSlug);
            $baguette->setBoquette($this->getBoquette());
            $baguette->setValid(true);
            $this->em->persist($baguette);
            $this->em->flush();
        }

        return $baguette;
    }

    public function getNbJoursOuvres(\DateTime $dateFin)
    {
        // on va chercher les vacances
        $repositoryVacances = $this->em->getRepository('PJMAppBundle:Vacances');
        $listeVacances = $repositoryVacances->findByFait(false);

        //période entre aujourd'hui et la fin
        $now = new \DateTime('now');
        $now->setTime(0, 0, 0);
        $dateFin->setTime(0, 0, 1);
        $period = new \DatePeriod(
            $now,
            new \DateInterval('P1D'),
            $dateFin
        );

        $nbJoursOuvres = 0;

        // pour tous les jours jusqu'à aujourd'hui, on débite
        foreach ($period as $date) {
            // si le jour n'est pas un samedi/dimanche
            if ($date->format('D') != 'Sat' && $date->format('D') != 'Sun') {
                // pour chaque vacances pas encore finies
                foreach ($listeVacances as $vacances) {
                    if ($date <= $vacances->getDateFin() && $date >= $vacances->getDateDebut()) {
                        --$nbJoursOuvres;
                    }
                }

                ++$nbJoursOuvres;
            }
        }

        return $nbJoursOuvres;
    }

    public function getCommande(User $user)
    {
        $repository = $this->em->getRepository('PJMAppBundle:Commande');
        $commandes = $repository->findByUserAndItemSlug($user, $this->itemSlug);

        foreach ($commandes as $commande) {
            if (!isset($active) && $commande->getValid()) {
                $active = $commande->getNombre() / 10;
            }

            if (!isset($attente) && null === $commande->getValid()) {
                $attente = $commande->getNombre() / 10;
            }

            if (isset($active) && isset($attente)) {
                break;
            }
        }

        return array(
            'active' => isset($active) ? $active : 0,
            'attente' => isset($attente) ? $attente : null,
        );
    }
}
