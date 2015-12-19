<?php

namespace PJM\AppBundle\Command\Migration;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NumsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('migration:nums')
            ->setDescription("Parse les num's de ceux qui n'en n'ont pas à partir des fam's.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $trads = $this->getContainer()->get('pjm.services.trads');
        $em = $this->getContainer()->get('doctrine')->getManager();

        $users = $em->getRepository('PJMAppBundle:User')->findBy(array(
            'nums' => null,
        ));

        if (!empty($users)) {
            foreach ($users as $user) {
                $user->setNums($trads->getNums($user->getFams()));
                $em->persist($user);
            }

            $em->flush();
            $output->writeln('[migration:nums] '.count($users).' success.');
        } else {
            $output->writeln('[migration:nums] No need to migrate.');
        }
    }
}
