<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Entity\Inbox\Inbox;
use PJM\AppBundle\Entity\Compte;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $proms = $this->container->get("pjm.services.group")->getProms1A();

        $userManager = $this->container->get('fos_user.user_manager');

        $users = array(
            // membre conscrit (aucun rôles)
            array(
                "username" => "conscrit",
                "bucque" => "Conscrit",
                "fams" => "1",
                "tabagns" => "bo",
                "proms" => $proms,
                "prenom" => "1A",
                "nom" => "de Bordeaux",
                "appartement" => "A218",
                "classe" => "SI3",
                "genre" => 0,
                "roles" => array(),
            ),
            // membre ancien (admin)
            array(
                "username" => "ancien",
                "bucque" => "Ancien",
                "fams" => "2",
                "tabagns" => "bo",
                "proms" => $proms-1,
                "prenom" => "2A",
                "nom" => "de Bordeaux",
                "appartement" => "B120",
                "classe" => "SI2, GIP1",
                "genre" => 0,
                "roles" => array("ROLE_ADMIN"),
            ),

            // membre ancienne (ZiCom Asso)
            array(
                "username" => "ancienne",
                "bucque" => "Ancienne",
                "fams" => "3",
                "tabagns" => "bo",
                "proms" => $proms-1,
                "prenom" => "2A",
                "nom" => "de Bordeaux",
                "appartement" => "B106",
                "classe" => "ST1, GM2",
                "genre" => 1,
                "roles" => array("ROLE_ASSO_COM"),
            ),

            // membre ancien de Angers (aucun rôle)
            array(
                "username" => "ancienAngers",
                "bucque" => "AncienAngers",
                "fams" => "4",
                "tabagns" => "an",
                "proms" => $proms-1,
                "prenom" => "2A",
                "nom" => "d'Angers",
                "appartement" => "B106",
                "classe" => "SI1, GM2",
                "genre" => 0,
                "roles" => array(),
            ),

            // membre p3 (Beta testeur)
            array(
                "username" => "p3",
                "bucque" => "P3",
                "fams" => "5",
                "tabagns" => "bo",
                "proms" => $proms-2,
                "prenom" => "3A",
                "nom" => "de Bordeaux",
                "appartement" => "B108",
                "classe" => "SI2, GIP2",
                "genre" => 0,
                "roles" => array("ROLE_BETA"),
            ),

            // membre archi (aucun rôle)
            array(
                "username" => "archi",
                "bucque" => "Archi",
                "fams" => "0",
                "tabagns" => "bo",
                "proms" => $proms-3,
                "prenom" => "Ancien élève",
                "nom" => "de Bordeaux",
                "appartement" => "Paris",
                "classe" => "SI3, GIP3, GTL",
                "genre" => 0,
                "roles" => array(),
            ),
        );

        $boquetteComptes = array(
            $this->getReference('pians-boquette'),
            $this->getReference('brags-boquette'),
            $this->getReference('paniers-boquette'),
        );

        foreach ($users as $userData) {
            $user = new User();

            $user->setEnabled(true);
            $user->setUsername($userData["username"]);
            $user->setEmail($userData["username"]."-test@yopmail.com");
            $user->setPlainPassword("test");
            $user->setRoles($userData["roles"]);

            $user->setBucque($userData["bucque"]);
            $user->setFams($userData["fams"]);
            $user->setTabagns($userData["tabagns"]);
            $user->setProms($userData["proms"]);

            $user->setPrenom($userData["prenom"]);
            $user->setNom($userData["nom"]);

            $user->setAppartement($userData["appartement"]);
            $user->setClasse($userData["classe"]);
            $user->setGenre($userData["genre"]);

            $inbox = new Inbox();
            $user->setInbox($inbox);

            foreach ($boquetteComptes as $boquette) {
                $compte = new Compte($user, $boquette);
                $compte->setSolde(2000);
                $manager->persist($compte);
            }

            $userManager->updateUser($user);

            $this->addReference($userData["username"].'-user', $user);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
