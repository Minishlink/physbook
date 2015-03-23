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
use PJM\AppBundle\Entity\Commande;
use PJM\AppBundle\Entity\Transaction;

class BragsResetCommand extends ContainerAwareCommand
{
    protected $em;
    protected $logger;
    protected $phpExcel;

    protected function configure()
    {
        $this
            ->setName('brags:reset')
            ->setDescription("Supprime les débits, mets le solde à la somme des crédits. ATTENTION, il faut que les commandes et prix de la baguette soient bien définis.")
            ->addOption('test', null, InputOption::VALUE_NONE, 'Si défini, pas de flush')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->logger = $this->getContainer()->get('logger');
        $this->phpExcel = $this->getContainer()->get('phpexcel');

        $slug = 'brags';
        $itemSlug = 'baguette';

        $repo_compte = $this->em->getRepository('PJMAppBundle:Compte');
        $repo_transaction = $this->em->getRepository('PJMAppBundle:Transaction');
        $repo_historique = $this->em->getRepository('PJMAppBundle:Historique');

        // on supprime tous les historiques du brags
        // TODO

        // on va chercher les comptes brags
        $comptes = $repo_compte->findByBoquetteSlug('brags');

        if ($comptes !== null) {
            foreach ($comptes as $compte) {
                $somme = $repo_transaction->findByCompteAndValid($compte, "OK");
                $compte->setSolde($somme);
                $this->em->persist($compte);
            }

            if (!$input->getOption('test')) {
                $this->em->flush();
            }
        }
    }
}
