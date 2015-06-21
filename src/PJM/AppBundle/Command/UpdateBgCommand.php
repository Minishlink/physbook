<?php

namespace PJM\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateBgCommand extends ContainerAwareCommand
{
    protected $em;
    protected $logger;
    protected $random;

    protected function configure()
    {
        $this
            ->setName('update:bg')
            ->setDescription("Tire au sort une nouvelle photo Bonjour Gadz'Arts.")
            ->addOption('test', null, InputOption::VALUE_NONE, 'Si défini, pas de flush')
            ->addOption('show', null, InputOption::VALUE_NONE, 'Si défini, affiche la photo choisie')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Tirage au sort photo Bonjour Gadz'Arts...");
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->logger = $this->getContainer()->get('logger');
        $this->random = $this->getContainer()->get('pjm.services.random');

        $photo_repo = $this->em->getRepository('PJMAppBundle:Media\Photo');
        $photosBDD = $photo_repo->findByPublication(2);

        // on calcule la moyenne des HM photos avec > 0 HM
        $nbTotalPhotosPlusZeroHM = 0;
        $totalUsersHM = 0;
        foreach ($photosBDD as $photo) {
            $nbUsersHM = $photo->getNbUsersHM();

            if ($nbUsersHM > 0) {
                ++$nbTotalPhotosPlusZeroHM;
                $totalUsersHM += $nbUsersHM;
            } else {
                // on indique qu'une photo avec 0 HM a été trouvée
                $photoZeroHM = true;
            }
        }

        $moy = ($nbTotalPhotosPlusZeroHM != 0) ? $totalUsersHM / $nbTotalPhotosPlusZeroHM : 0;
        $moyBasse = round(0.8 * $moy);
        $moyHaute = round(1.2 * $moy);

        // on attribue les probabilités
        foreach ($photosBDD as $photo) {
            $nbUsersHM = $photo->getNbUsersHM();

            if ($nbUsersHM == 0) {
                $proba = 0.8;
            } else {
                if ($photoZeroHM) {
                    $proba = 0.2;
                }

                if ($nbUsersHM < $moyBasse) {
                    $proba *= 0.1;
                } elseif ($nbUsersHM > $moyHaute) {
                    $proba *= 0.5;
                } else {
                    $proba *= 0.4;
                }
            }

            $photos[] = $photo;
            $probas[] = round($proba * 100);
        }

        // on tire la photo
        $photo = $this->random->weightedRandom($photos, $probas);

        if ($input->getOption('show')) {
            $output->writeln($photo->getId().': '.$photo->getLegende());
        }

        // on change la photo Bonjour Gadz'Arts
        $photoAChanger = $photo_repo->findOneByPublication(3);
        if ($photoAChanger !== null) {
            $photoAChanger->setPublication(2);
            $this->em->persist($photoAChanger);
        }
        $photo->setPublication(3);
        $this->em->persist($photo);

        if (!$input->getOption('test')) {
            $this->em->flush();
        }
    }
}
