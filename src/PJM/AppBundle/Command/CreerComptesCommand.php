<?php

namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use PJM\AppBundle\Entity\Compte;

class CreerComptesCommand extends ContainerAwareCommand
{
    protected $em;
    protected $logger;

    protected function configure()
    {
        $this
            ->setName('users:create:compte')
            ->setDescription("Créer les comptes des PGs qui n'en n'ont pas")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->logger = $this->getContainer()->get('logger');

        // les boquettes concernées :
        $repository = $this->em->getRepository('PJMAppBundle:Boquette');
        $boquettes = array(
            $repository->findOneBySlug('pians'),
            $repository->findOneBySlug('paniers'),
            $repository->findOneBySlug('brags'),
        );

        // on va chercher les users
        $users = $this->em->getRepository('PJMUserBundle:User')->findAll();

        if (!empty($users)) {
            $repository = $this->em->getRepository('PJMAppBundle:Compte');
            foreach ($users as $user) {
                // on va chercher les comptes
                $comptes = $repository->findByUser($user);

                if (empty($comptes)) {
                    // on crée les comptes
                    foreach ($boquettes as $boquette) {
                        $nvCompte = new Compte($user, $boquette);
                        $this->em->persist($nvCompte);
                        $this->logger->info('NEW Nouveau compte : '.$nvCompte);
                    }
                }
            }

            $this->em->flush();
        }
    }
}
