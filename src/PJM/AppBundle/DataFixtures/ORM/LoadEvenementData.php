<?php

namespace PJM\AppBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\AppBundle\Entity\Event\Evenement;

class LoadEvenementData extends BaseFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $events = array(
            array(
                'createur' => 'ancien',
                'nom' => 'D\'runssage',
                'description' => 'Dans la bonne humeur !',
                'lieu' => 'Pian\'s',
                'day' => false,
                'boquette' => $this->getBoquette("pians"),
                'public' => true,
                'prix' => '0',
                'majeur' => false,
            ),
            array(
                'createur' => 'p3',
                'nom' => 'Apéro Phy\'sbook',
                'description' => '',
                'lieu' => 'Chez le phy\'sss',
                'day' => false,
                'boquette' => $this->getBoquette("physbook"),
                'public' => true,
                'prix' => '0',
                'majeur' => false,
            ),
            array(
                'createur' => 'ancienne',
                'nom' => 'Grandes UAIs',
                'description' => '',
                'lieu' => '',
                'day' => true,
                'boquette' => null,
                'public' => true,
                'prix' => '4000',
                'majeur' => true,
            ),
            array(
                'createur' => 'ancien',
                'nom' => 'Bap\'s des 215',
                'description' => 'Ou pas ;)',
                'lieu' => '',
                'day' => false,
                'boquette' => null,
                'public' => false,
                'prix' => '0',
                'majeur' => true,
            ),
            array(
                'createur' => 'ancien',
                'nom' => 'Nuit des Fignos',
                'description' => '',
                'lieu' => 'H14',
                'day' => false,
                'boquette' => null,
                'public' => true,
                'prix' => '0',
                'majeur' => true,
            ),
        );

        foreach ($events as $event) {
            $this->loadEvenement(
                $manager,
                $event['createur'],
                $event['nom'],
                $event['description'],
                $event['day'],
                $event['boquette'],
                $event['public'],
                $event['prix'],
                $event['majeur']
            );
        }

        $manager->flush();
    }

    private function loadEvenement(ObjectManager $manager, $createur, $nom, $description, $day, $boquette, $public, $prix, $majeur)
    {
        if (empty($description)) {
            $description = $this->getLoremIpsum(rand(1,2), "paragraphs");
        }

        $event = new Evenement();
        $event->setCreateur($this->getUser($createur));
        $event->setNom($nom);
        $event->setDescription($description);
        $event->setBoquette($boquette);
        $event->setPublic($public);

        $event->setPrix($prix);
        $event->setMajeur($majeur);

        $dateDebut = $this->getRandomDate(0, 30, rand(0,1));
        $event->setDateDebut($dateDebut);

        $intervalDebutFin = $day ? "P".rand(1,2)."D" : "PT".rand(1,12)."H";
        $event->setDateFin($dateDebut->add(new \DateInterval($intervalDebutFin)));

        $event->setDay($day);

        $manager->persist($event);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}