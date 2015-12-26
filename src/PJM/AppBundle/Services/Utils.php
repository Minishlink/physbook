<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Historique;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Twig\IntranetExtension;

class Utils
{
    protected $em;
    protected $mailer;
    protected $twigExt;
    protected $rezal;

    public function __construct(EntityManager $em, Mailer $mailer, IntranetExtension $twigExt, Rezal $rezal)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->twigExt = $twigExt;
        $this->rezal = $rezal;
    }

    public function getHistorique(User $user, $boquetteSlug, $limit = null)
    {
        $compte = $this->em
            ->getRepository('PJMAppBundle:Compte')
            ->findOneByUserAndBoquetteSlug($user, $boquetteSlug)
        ;

        $debits = $this->em
            ->getRepository('PJMAppBundle:Historique')
            ->findByUserAndBoquetteSlug($user, $boquetteSlug, $limit, true)
        ;

        $debitsFormates = array();

        foreach ($debits as $k => $debit) {
            $debitsFormates[$k]['date'] = $debit->getDate();
            $debitsFormates[$k]['nom'] = $debit->getItem()->getLibelle().' ('.($debit->getNombre() / 10).')';
            $debitsFormates[$k]['montant'] = '-'.$this->twigExt->prixFilter($debit->getItem()->getPrix() * $debit->getNombre() / 10);
            $debitsFormates[$k]['infos'] = $debit->getItem()->getInfos();
        }
        unset($debits);

        // cvis appartient au pians pour les crédits
        if ($boquetteSlug == 'cvis') {
            $boquetteSlug = 'pians';
        }

        $credits = $this->em
            ->getRepository('PJMAppBundle:Transaction')
            ->findByUserAndBoquetteSlug($user, $boquetteSlug, $limit, 'OK')
        ;

        $creditsFormates = array();

        foreach ($credits as $k => $credit) {
            $creditsFormates[$k]['date'] = $credit->getDate();
            $creditsFormates[$k]['nom'] = $this->twigExt->moyenPaiementFilter($credit->getMoyenPaiement());

            $creditsFormates[$k]['montant'] = ($credit->getMontant() >= 0) ? '+' : '';
            $creditsFormates[$k]['montant'] .= $this->twigExt->prixFilter($credit->getMontant());

            $creditsFormates[$k]['infos'] = $credit->getInfos();
        }
        unset($credits);

        $transferts = $this->em
            ->getRepository('PJMAppBundle:Consos\Transfert')
            ->findByCompte($compte, $limit);

        $transfertsFormates = array();

        foreach ($transferts as $k => $transfert) {
            $recu = ($transfert->getReceveur() === $compte);
            $dest = $recu ? $transfert->getEmetteur()->getUser() : $transfert->getReceveur()->getUser();
            $annule = ($transfert->getStatus() != 'OK');

            $transfertsFormates[$k]['date'] = $transfert->getDate();

            $transfertsFormates[$k]['nom'] = 'Transfert ';
            $transfertsFormates[$k]['nom'] .= $recu ? 'de ' : 'à ';
            $transfertsFormates[$k]['nom'] .= $dest->getBucque().' '.$dest->getUsername();

            $transfertsFormates[$k]['montant'] = $recu ? '+' : '-';
            $transfertsFormates[$k]['montant'] .= $this->twigExt->prixFilter($transfert->getMontant());

            if ($annule) {
                if (substr($transfert->getStatus(), 0, 1) != '3') {
                    $transfertsFormates[$k]['nom'] .= ' (annulé)';
                }
                $transfertsFormates[$k]['infos'] = 'Erreur : '.$transfert->getStatus();
            } else {
                $transfertsFormates[$k]['infos'] = $transfert->getRaison();
            }
        }
        unset($transferts);

        $liste = array_merge($debitsFormates, $creditsFormates, $transfertsFormates);
        unset($debitsFormates);
        unset($creditsFormates);
        unset($transfertsFormates);

        $sort_function = function ($a, $b) {
            $diff = $a['date']->diff($b['date']);

            return $diff->invert ? '-1' : '1';
        };
        usort($liste, $sort_function);

        if (isset($limit)) {
            return array_slice($liste, 0, $limit);
        }

        return $liste;
    }

    /**
     * Retourne l'item du moment pour une boquette.
     *
     * @param string $boquetteSlug Slug de la boquette
     *
     * @return object|null Item du moment ou null si introuvable
     */
    public function getFeaturedItem($boquetteSlug)
    {
        $featuredItem = $this->em
            ->getRepository('PJMAppBundle:FeaturedItem')
            ->findByBoquetteSlug($boquetteSlug, true);

        return (isset($featuredItem)) ? $featuredItem->getItem() : null;
    }

    public function bucquage($boquetteSlug, $itemSlug)
    {
        $boquette = $this->em->getRepository('PJMAppBundle:Boquette')
            ->findOneBySlug($boquetteSlug);
        $repositoryHistorique = $this->em->getRepository('PJMAppBundle:Historique');
        $repositoryCommande = $this->em->getRepository('PJMAppBundle:Commande');
        $repositoryCompte = $this->em->getRepository('PJMAppBundle:Compte');
        $repositoryVacances = $this->em->getRepository('PJMAppBundle:Vacances');
        $listeUsers = [];

        // on regarde quand a été fait le dernier bucquage
        $lastBucquage = $repositoryHistorique->findLastValidByItemSlug($itemSlug);
        $now = new \DateTime('now');
        $now->setTime(0, 0, 0);
        // s'il y a déjà eu un bucquage
        if (isset($lastBucquage)) {
            // si ce bucquage a été aujourd'hui, on arrête
            if ($lastBucquage->getDate()->setTime(0, 0, 0) == $now) {
                return 'Un bucquage a deja ete fait aujourd\'hui.';
            }

            // sinon on compte le nombre de jours à bucquer
            $startDate = $lastBucquage->getDate()->setTime(0, 0, 0)->add(new \DateInterval('P1D'));
            $nbJours = $startDate->diff($now, true)->days + 1;
        } else {
            // sinon on bucque le premier jour
            $startDate = $now;
            $nbJours = 1;
        }
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P'.$nbJours.'D'));

        // on obtient tous les jours à bucquer (contient encore les WE)
        $period = new \DatePeriod(
            $startDate,
            new \DateInterval('P1D'),
            $endDate
        );

        // on va chercher les vacances
        $listeVacances = $repositoryVacances->findByFait(false);

        // pour tous les jours jusqu'à aujourd'hui, on débite
        foreach ($period as $date) {
            // si le jour n'est pas un samedi/dimanche
            if ($date->format('D') != 'Sat' && $date->format('D') != 'Sun') {
                $jourDeVacs = false; // par défaut

                // pour chaque vacances pas encore finies
                foreach ($listeVacances as $vacances) {
                    // si le dernier jour de ces vacances est passé
                    if ($vacances->getDateFin() < $date) {
                        // on indique les vacances comme finies
                        $vacances->setFait(true);
                        $this->em->persist($vacances);
                    }

                    $endDateFinVacs = clone $vacances->getDateFin();
                    $endDateFinVacs->add(new \DateInterval('P1D'));
                    $periodVacs = new \DatePeriod(
                        $vacances->getDateDebut(),
                        new \DateInterval('P1D'),
                        $endDateFinVacs
                    );

                    // pour chaque jour de vacances
                    foreach ($periodVacs as $dateVacs) {
                        // si le jour en train d'être bucqué est un jour de vacances
                        if ($date == $dateVacs) {
                            // on l'indique
                            $jourDeVacs = true;
                            // on a un jour de vacances donc on a notre info, on arrête le bouclage
                            break 2;
                        }
                    }
                }

                // si le jour n'est pas un jour de vacances
                if (!$jourDeVacs) {
                    // on regarde les commandes actives et résiliées à cette date
                    $commandes = array_merge(
                        $repositoryCommande->findByItemSlugAndValidAndAtDate($itemSlug, true, $date),
                        $repositoryCommande->findByItemSlugAndValidAndAtDate($itemSlug, false, $date)
                    );

                    foreach ($commandes as $commande) {
                        // bucquer dans l'historique
                        $historique = new Historique();
                        $historique->setDate($date);
                        $historique->setCommande($commande);
                        $historique->setValid(true);
                        $this->em->persist($historique);

                        // on enregistre l'utilisateur comme "à regarder" pour le negat'ss
                        if (!in_array($historique->getUser(), $listeUsers)) {
                            $listeUsers[] = $historique->getUser();
                        }
                    }
                } else {
                    --$nbJours;
                }
            } else {
                // si c'est un samedi ou un dimanche on compte un jour en moins
                --$nbJours;
            }
        }

        // propagation en BDD des débits
        $this->em->flush();

        // pour tous ceux qui ont été débité,
        // on check les comptes en negat'ss et envoit un mail
        foreach ($listeUsers as $user) {
            $compte = $repositoryCompte->findOneByUserAndBoquetteSlug($user, $boquette->getSlug());
            if ($compte->getSolde() < -500) {
                $this->mailer->sendAlerteSolde($compte);
            }
        }

        return $nbJours.' jours bucques a partir du '.$startDate->format('d/m/y').'.';
    }
}
