<?php

namespace PJM\AppBundle\Services\Boquette;

use Doctrine\ORM\EntityManager;

use PJM\UserBundle\Entity\User;
use PJM\AppBundle\Entity\Compte;

class BoquetteService
{
    protected $em;
    protected $slug;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getItem($itemSlug, $valid = true)
    {
        $rep = $this->em->getRepository('PJMAppBundle:Item');

        if ($valid === 'any') {
            return $rep->findOneBySlug($itemSlug);
        }

        return $rep->findOneBy(array('slug' => $itemSlug, 'valid' => $valid));
    }

    public function getBoquette($boquetteSlug = null)
    {
        if (isset($boquetteSlug)) {
            $this->slug = $boquetteSlug;
        }

        $boquette = $this->em
            ->getRepository('PJMAppBundle:Boquette')
            ->findOneBySlug($this->slug)
        ;

        return $boquette;
    }

    public function getCompte(User $user, $boquetteSlug = null)
    {
        if (isset($boquetteSlug)) {
            $this->slug = $boquetteSlug;
        }

        // Le C'vis est dans le compte du Pian's
        if ($this->slug == "cvis") {
            $this->slug = "pians";
        }

        $boquette = $this->em->getRepository('PJMAppBundle:Boquette')
            ->findOneBySlug($this->slug);

        $compte = $this->em->getRepository('PJMAppBundle:Compte')
            ->findOneBy(array('user' => $user, 'boquette' => $boquette));

        if ($compte === null) {
            $compte = new Compte($user, $boquette);
            $this->em->persist($compte);
            $this->em->flush();
        }

        return $compte;
    }

    public function getSolde(User $user, $boquetteSlug = null)
    {
        return $this->getCompte($user, $boquetteSlug)->getSolde();
    }

    public function getItems($valid = true, $limit = null, $offset = null, $boquetteSlug = null)
    {
        if (isset($boquetteSlug)) {
            $this->slug = $boquetteSlug;
        }

        return $this->em->getRepository('PJMAppBundle:Item')
            ->findByBoquetteSlug($this->slug, true, null, $offset);
    }

    public function compterAchatsItem($itemSlug, $month = null, $year = null)
    {
        $nb = $this->em->getRepository('PJMAppBundle:Historique')
            ->countByItemSlug($itemSlug, $month, $year);

        return $nb;
    }

    public function compterAchatsBoquette($month = null, $year = null, $boquetteSlug = null)
    {
        if (isset($boquetteSlug)) {
            $this->slug = $boquetteSlug;
        }

        $nb = $this->em->getRepository('PJMAppBundle:Historique')
            ->countByBoquetteSlug($boquetteSlug, $month, $year);

        return $nb;
    }

    /**
     * Cherche les utilisateurs avec le plus d'achats
     * @param  integer [$month = null] Mois à chercher. Si $year null, mois de l'année en cours, sinon année $year. Si $month est null, se réferer à year.
     * @param  integer [$year  = null]  Année à chercher. Si $year null, cherche depuis toujours.
     * @return array   Matrice d'utilisateurs avec leur nombre d'achats associés, triés par ce nombre.
     */
    public function getTopConsommateurs($month = null, $year = null) {
        $res = $this->em->getRepository('PJMAppBundle:Historique')
            ->getTopUsers($this->slug, 3, $month, $year);

        $topConsommateurs = array();
        foreach ($res as $row) {
            $topConsommateurs[] = array(
                'user' => $row[0]->getUser(),
                'somme' => $row["somme"]/10
            );
        }

        return $topConsommateurs;
    }
}
