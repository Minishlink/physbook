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

class RezalPurgeCommand extends ContainerAwareCommand
{
    protected $em;
    protected $logger;

    protected function configure()
    {
        $this
            ->setName('rezal:purge')
            ->setDescription("Efface tous les historiques du pian's et c'vis sur le serveur Phy'sbook")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->logger = $this->getContainer()->get('logger');

        $repository = $this->em->getRepository('PJMAppBundle:Historique');

        // les boquettes concernées :
        $boquettes = array(
            'pians',
            'cvis'
        );

        foreach ($boquettes as $boquette) {
            $historiques = $repository->findByBoquetteSlug($boquette);

            foreach ($historiques as $historique) {
                $this->em->remove($historique);
            }
        }
        $this->em->flush();
    }
}
