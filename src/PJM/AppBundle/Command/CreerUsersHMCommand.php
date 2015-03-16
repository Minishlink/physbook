<?php
namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\HttpFoundation\Request;

use PJM\AppBundle\Entity\UsersHM;

class CreerUsersHMCommand extends ContainerAwareCommand
{
    protected $em;
    protected $logger;

    protected function configure()
    {
        $this
            ->setName('users:create:HM')
            ->setDescription("Créer les liens usersHM manquants")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->logger = $this->getContainer()->get('logger');

        // on va chercher les articles
        $res = $this->em->getRepository('PJMAppBundle:Actus\Article')->findAll();
        $this->boucle($res);

        // pareil avec les items
        $res = $this->em->getRepository('PJMAppBundle:Item')->findAll();
        $this->boucle($res);

        $this->em->flush();
    }

    private function boucle($res) {
        if (!empty($res)) {
            foreach ($res as $r) {
                $usersHM = $r->getUsersHM();
                if($usersHM == null) {
                    //on crée le lien
                    $usersHM = new UsersHM();
                    $r->setUsersHM($usersHM);
                    $this->em->persist($r);
                }
            }
        }
    }
}
