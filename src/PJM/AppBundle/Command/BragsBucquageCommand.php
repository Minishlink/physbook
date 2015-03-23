<?php
namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\HttpFoundation\Request;

class BragsBucquageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('brags:bucquage')
            ->setDescription('Débite les comptes brags en fonction des commandes et des vacances.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $utils = $this->getContainer()->get('pjm.services.utils');
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        $msg = $utils->bucquage("brags", "baguette");
        $output->writeln($msg);
    }
}
