<?php

namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotificationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('notifications')
            ->setDescription('Lance des notifications pour les évènements à venir.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // on notifie pour les prochains évènements
        $this->getContainer()->get('pjm.services.evenement_manager')->notifyForNextEvents();
    }
}
