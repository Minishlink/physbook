<?php
namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\HttpFoundation\Request;

use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Compte;
use PJM\UserBundle\Entity\User;

class CreerComptesCommand extends ContainerAwareCommand
{
    protected $em;
    protected $rezal;
    protected $logger;

    protected function configure()
    {
        $this
            ->setName('users:create:compte')
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
