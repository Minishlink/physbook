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
use PJM\AppBundle\Entity\Commande;

class BragsForceCommand extends ContainerAwareCommand
{
    protected $em;
    protected $logger;
    protected $phpExcel;

    protected function configure()
    {
        $this
            ->setName('brags:force')
            ->setDescription("Lit url et ajoute les commandes/soldes en fonction de l'user puis supprime url. ATTENTION, vérifier que les commandes en cours de ces utilisateurs sont en attente ou résiliées.")
            ->addArgument('url', InputArgument::OPTIONAL, 'Où est le .xlsx ?')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Si défini, la réponse est affichée en majuscules')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->logger = $this->getContainer()->get('logger');
        $this->phpExcel = $this->getContainer()->get('phpexcel');

        $slug = 'brags';
        $itemSlug = 'baguette';
        $url = $input->getArgument('url');

        if ($url == "") {
            $dialog = $this->getHelperSet()->get('dialog');
            $url = $dialog->ask(
                $output,
                "Indique l'URL absolu du fichier xlsx :\n"
            );
        }

        //debug
        $url = 'C:\Users\Louis\Documents\Programmation\Sites\pjm-intranet\web\test.xlsx';

        if (!file_exists($url)) {
            $this->logger->warn("Le fichier '".$url."' n'existe pas !");
            return;
        }

        $phpExcelObject = $this->phpExcel->createPHPExcelObject($url);

        $sheetData = $phpExcelObject->getActiveSheet()->toArray(null,true,true,true);

        $user_repo = $this->em->getRepository('PJMUserBundle:User');
        $compte_repo = $this->em->getRepository('PJMAppBundle:Compte');

        $baguette = $this->em->getRepository('PJMAppBundle:Item')->findOneBy(array('slug' => $itemSlug, 'valid' => true));

        if ($baguette === null) {
            $this->logger->error('Baguette non trouvee');
            return;
        }

        foreach ($sheetData as $row) {
            $users = array();
            $prenomnoms = explode(", ", $row["A"]);
            if ($prenomnoms !== null) {
                foreach ($prenomnoms as $prenomnom) {
                    if (preg_match("/[a-zA-Z]+ ([a-zA-Z éè\-]+)+/", $prenomnom, $id)) {
                        if (isset($id[1])) {
                            $user = $user_repo->findOneByNom($id[1]);
                            if ($user === null) {
                                $output->writeln("User '". $id[1] ."' non trouvé avec nom");

                                $user = $user_repo->findOneByUsername($id[1]);
                                if ($user === null) {
                                    $output->writeln("User '". $id[1] ."' non trouvé avec username");
                                }
                            } else {
                                $users[] = $user;
                            }
                        }
                    }
                }

                if (!empty($users)) {
                    $nombre = $row["C"]*10;
                    $kagib = $row["B"];
                    $solde = $row["D"]*100;
                    $date = $row["E"];

                    if (empty($nombre) && $nombre !== 0) {
                        $this->logger->error("Il manque la commande pour ".$row["A"]);
                    } else if (empty($solde) && $solde !== 0) {
                        $this->logger->error("Il manque le solde pour ".$row["A"]);
                    } else if (empty($date)) {
                        $this->logger->error("Il manque la date pour ".$row["A"]);
                    }

                    $ok = false;

                    foreach ($users as $user) {
                        // si on a précisé un kagib on le remplace
                        if (!empty($kagib)) {
                            if (substr($user->getAppartement(), 0, 4) != $kagib) {
                                $user->setAppartement($kagib);
                                $this->em->persist($user);
                            }
                        }

                        if (!$ok) {
                            if (empty($user->getAppartement())) {
                                $this->logger->warn($user." ne peut pas prendre de commande car il n'a pas d'appartement.");
                                return;
                            }

                            // on modifie la commande
                            $commande = new Commande();
                            $commande->setItem($baguette);
                            $commande->setNombre($nombre);
                            $commande->setDateDebut(new \DateTime($date));
                            $commande->setValid(true);
                            $commande->setUser($user);
                            $this->em->persist($commande);

                            // on modifie le solde
                            $compte = $compte_repo->findOneByUserAndBoquetteSlug($user, $slug);
                            $compte->crediter($solde);
                            $this->em->persist($compte);

                            $ok = true;
                        }
                    }
                }
            }
        }

        if (!$input->getOption('test')) {
            $this->em->flush();
        }
    }
}
