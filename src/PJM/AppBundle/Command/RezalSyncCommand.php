<?php
namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\HttpFoundation\Request;

class RezalSyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rezal:sync')
            ->setDescription("Synchronise la BDD Phy'sbook avec celle du Rezal")
            ->addArgument(
                'boquetteSlug',
                null,
                'Boquette à synchroniser ? (pians|cvis)'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $utils = $this->getContainer()->get('pjm.services.utils');
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        $output->writeln("DEBUT rezal:sync");
        $output->writeln("DEBUT syncRezalProduits Pians");
        $msg = $utils->syncRezalProduits('pians');
        $output->writeln($msg);
        $output->writeln("FIN syncRezalProduits Pians");
        $output->writeln("DEBUT syncRezalProduits Cvis");
        $msg = $utils->syncRezalProduits('cvis');
        $output->writeln($msg);
        $output->writeln("FIN syncRezalProduits Cvis");
        $output->writeln("DEBUT syncRezalHistorique");
        $msg = $utils->syncRezalHistorique();
        $output->writeln($msg);
        $output->writeln("FIN syncRezalProduits Historique");
        $output->writeln("FIN rezal:sync");
    }
}
