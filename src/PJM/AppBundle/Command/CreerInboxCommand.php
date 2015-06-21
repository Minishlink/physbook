<?php

namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use PJM\AppBundle\Entity\Inbox\Inbox;

class CreerInboxCommand extends ContainerAwareCommand
{
    protected $em;
    protected $logger;

    protected function configure()
    {
        $this
            ->setName('users:create:inbox')
            ->setDescription("Créer l'inbox des PGs qui n'en n'ont pas")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->logger = $this->getContainer()->get('logger');

        // on va chercher les users
        $users = $this->em->getRepository('PJMUserBundle:User')->findAll();

        if (!empty($users)) {
            foreach ($users as $user) {
                $inbox = $user->getInbox();
                if ($inbox === null) {
                    //on crée l'inbox
                    $inbox = new Inbox();
                    $user->setInbox($inbox);
                    $this->em->persist($user);
                }
            }

            $this->em->flush();
        }
    }
}
