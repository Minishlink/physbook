<?php
namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\HttpFoundation\Request;

use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Historique;
use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Entity\Compte;
use PJM\AppBundle\Entity\Item;
use PJM\UserBundle\Entity\User;

class RezalSyncCommand extends ContainerAwareCommand
{
    protected $em;
    protected $rezal;
    protected $logger;

    protected function configure()
    {
        $this
            ->setName('rezal:sync')
            ->setDescription("Synchronise la BDD Phy'sbook avec celle du Rezal")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->rezal = $this->getContainer()->get('pjm.services.rezal');
        $this->logger = $this->getContainer()->get('logger');

        $output->writeln("DEBUT rezal:sync");

        $output->writeln("DEBUT syncRezalProduits Pians");
        $this->syncRezalProduits('pians');
        $output->writeln("FIN syncRezalProduits Pians");

        $output->writeln("DEBUT syncRezalProduits Cvis");
        $this->syncRezalProduits('cvis');
        $output->writeln("FIN syncRezalProduits Cvis");

        $output->writeln("DEBUT syncRezalHistorique Pians");
        $this->syncRezalHistorique('pians');
        $output->writeln("FIN syncRezalHistorique Pians");

        $output->writeln("DEBUT syncRezalHistorique Cvis");
        $this->syncRezalHistorique('cvis');
        $output->writeln("FIN syncRezalHistorique Cvis");

        $output->writeln("DEBUT syncRezalCompte Pi");
        $this->syncRezalCompte();
        $output->writeln("FIN syncRezalCompte Pi");

        $output->writeln("FIN rezal:sync");
    }

    public function syncRezalProduits($boquetteSlug)
    {
        // TODO Catégories

        if ($boquetteSlug == "pians" || $boquetteSlug == "cvis") {
            $repository = $this->em->getRepository('PJMAppBundle:Item');

            // on va chercher les produits existants sur Phy'sbook
            $listeProduitsPhysbook = $repository->findByBoquetteSlug($boquetteSlug, true);
            $existants = "";
            if ($listeProduitsPhysbook !== null) {
                foreach ($listeProduitsPhysbook as $k => $p) {
                    if ($k > 0) {
                        $existants .= ", ";
                    }
                    $existants .= "'".$p->getSlug()."'";
                }
            }

            // on va chercher les produits du Rézal qui ne sont pas sur Phy'sbook
            $listeNvProduitsRezal = $this->rezal->listeConsos($boquetteSlug, $existants, false);

            // on les ajoute sur Phy'sbook
            if ($listeNvProduitsRezal !== null) {
                foreach($listeNvProduitsRezal as $produit) {
                    $nvProduit = new Item();
                    $nvProduit->setSlug($produit['idObjet']);
                    $nvProduit->setLibelle($produit['intituleObjet']);
                    $nvProduit->setPrix($produit['prix']*100);
                    $nvProduit->setBoquette($this->getBoquette($boquetteSlug));
                    $this->logger->info("NEW: ".$nvProduit->getLibelle());
                    $this->em->persist($nvProduit);
                }
            }

            // on va chercher les autres produits déjà existants
            $listeProduitsRezal = $this->rezal->listeConsos($boquetteSlug, $existants, true);
            // on filtre par ceux dont le prix a changé et on les ajoute sur Phy'sbook
            if ($listeProduitsRezal !== null) {
                foreach($listeProduitsRezal as $produitRezal) {
                    foreach($listeProduitsPhysbook as $produitPhysbook) {
                        if ($produitRezal['idObjet'] == $produitPhysbook->getSlug()) {
                            if (round($produitRezal['prix']*100, 2) != $produitPhysbook->getPrix()) {
                                $nvProduit = clone $produitPhysbook;
                                $nvProduit->setPrix($produitRezal['prix']*100);
                                $this->logger->info("UPDATE: ".$nvProduit->getLibelle());
                                $produitPhysbook->setValid(false);
                                $this->em->persist($produitPhysbook);
                                $this->em->persist($nvProduit);
                            }
                        }
                    }
                }
            }

            // TODO les produits de Phy'sbook qui sont encore actifs alors qu'ils ont disparu du Rézal sont désactivés

            // on commit
            $this->em->flush();
        }
    }

    public function syncRezalHistorique($boquetteSlug)
    {
        if ($boquetteSlug == "pians" || $boquetteSlug == "cvis") {
            $repository = $this->em->getRepository('PJMAppBundle:Historique');
            $repositoryItem = $this->em->getRepository('PJMAppBundle:Item');
            $repositoryUser = $this->em->getRepository('PJMUserBundle:User');

            // on va chercher le dernier historique rentré dans la BDD Phy'sbook
            $lastHistorique = $repository->findLastValidByBoquetteSlug($boquetteSlug);
            $listeHistRezal = null;
            if ($lastHistorique !== null) {
                $date = $lastHistorique->getDate()->format('Y-m-d H:i');
                $listeHistRezal = $this->rezal->listeHistoriques($boquetteSlug, $date);
            } else {
                $listeHistRezal = $this->rezal->listeHistoriques($boquetteSlug);
            }

            // on récupère tous les nouveaux historiques sur la BDD R&z@l
            if ($listeHistRezal !== null) {
                foreach ($listeHistRezal as $historique) {
                    $nvHistorique = new Historique();
                    $item = $repositoryItem->findOneBy(array(
                        'slug' => $historique['objet'],
                        'valid' => true
                    ));
                    if ($item === null) {
                        $this->logger->error("Item non trouve ".$historique['objet']);
                        continue;
                    }
                    $nvHistorique->setItem($item);
                    $username = $historique['fams'].strtolower($historique['tbk']).$historique['proms'];
                    $user = $repositoryUser->findOneByUsername($username);
                    if ($user === null) {
                        $this->logger->error("User non trouve ".$username." (".$historique['date'].")");
                        continue;
                    }
                    $nvHistorique->setUser($user);
                    $nvHistorique->setDate(new \DateTime($historique['date']));
                    $nvHistorique->setNombre($historique['qte']*10);
                    $nvHistorique->setValid(true);
                    $this->em->persist($nvHistorique);
                }

                // on les ajoute à la BDD Phy'sbook
                $this->em->flush();
            }
        }
    }

    public function syncRezalCompte()
    {
        $repository = $this->em->getRepository('PJMAppBundle:Compte');

        // on récupère tous les comptes du R&z@l
        $listeComptes = $this->rezal->listeComptes();
        if ($listeComptes !== null) {
            foreach ($listeComptes as $compte) {
                // pour chaque compte on update le compte Phy'sbook
                $username = $compte['fams'].strtolower($compte['tbk']).$compte['proms'];
                $upCompte = $repository->findOneByUsernameAndBoquetteSlug($username, 'pians');
                if ($upCompte === null) {
                    $this->logger->error("Compte non trouve ".$username);
                    continue;
                }
                $upCompte->setSolde($compte['montant']);
                $this->em->persist($upCompte);
            }

            // on les ajoute à la BDD Phy'sbook
            $this->em->flush();
        }
    }
}
