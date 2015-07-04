<?php

namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('create:user')
            ->setDescription('Créer un utilisateur')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'E-mail'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Mot de passe'
            )
            ->addArgument(
                'fams',
                InputArgument::REQUIRED,
                "Fam's"
            )
            ->addArgument(
                'tabagns',
                InputArgument::REQUIRED,
                "Tabagn's"
            )
            ->addArgument(
                'proms',
                InputArgument::REQUIRED,
                "Prom's"
            )
            ->addArgument(
                'bucque',
                InputArgument::REQUIRED,
                'Bucque'
            )
            ->addArgument(
                'prenom',
                InputArgument::REQUIRED,
                'Prenom'
            )
            ->addArgument(
                'nom',
                InputArgument::REQUIRED,
                'Nom'
            )
            ->addOption(
               'super-admin',
               null,
               InputOption::VALUE_NONE,
               "Si defini, l'utilisateur sera super-admin"
            )->addOption(
               'admin',
               null,
               InputOption::VALUE_NONE,
               "Si defini, l'utilisateur sera admin"
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userManager = $this->getContainer()->get('pjm.services.user_manager');
        $user = $userManager->createUser();
        $user->setEmail($input->getArgument('email'));
        $user->setPlainPassword($input->getArgument('password'));
        $user->setFams($input->getArgument('fams'));
        $user->setTabagns($input->getArgument('tabagns'));
        $user->setProms($input->getArgument('proms'));
        $user->setBucque($input->getArgument('bucque'));
        $user->setPrenom($input->getArgument('prenom'));
        $user->setNom($input->getArgument('nom'));
        $userManager->configure($user);

        if ($input->getOption('super-admin')) {
            $user->addRole('ROLE_SUPER_ADMIN');
        }

        if ($input->getOption('admin')) {
            $user->addRole('ROLE_ADMIN');
        }

        $this->getContainer()->get('doctrine')->getManager()->flush();

        $output->writeln($user->getUsername().' has been created.');
    }
}
