<?php
namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\HttpFoundation\Request;

class BragsResetCommand extends ContainerAwareCommand
{
    protected $em;
    protected $logger;
    protected $phpExcel;

    protected function configure()
    {
        $this
            ->setName('brags:reset')
                        ->setDescription("Supprime les débits, mets le solde à la somme des crédits. ATTENTION, il faut que les commandes, les vacances (fait=0) et le prix de la baguette soient bien définis.")
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

        $repo_compte = $this->em->getRepository('PJMAppBundle:Compte');
        $repo_transaction = $this->em->getRepository('PJMAppBundle:Transaction');
        $repo_historique = $this->em->getRepository('PJMAppBundle:Historique');

        // on supprime tous les historiques du brags
        $historiques = $repo_historique->findByBoquetteSlug($slug);
        foreach ($historiques as $historique) {
            $this->em->remove($historique);
        }

        // on va chercher les comptes brags
        $comptes = $repo_compte->findByBoquetteSlug($slug);

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
