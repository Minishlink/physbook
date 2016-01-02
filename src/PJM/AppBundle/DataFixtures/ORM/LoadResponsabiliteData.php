<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\AppBundle\Entity\Responsabilite;

class LoadResponsabiliteData extends BaseFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $objects = array(
            array(
                'libelle' => 'ZiCdF',
                'boquette' => 'cdf',
                'niveau' => 0,
                'active' => true,
            ),
            array(
                'libelle' => 'ViZiCdF',
                'boquette' => 'cdf',
                'niveau' => 0,
                'active' => false,
            ),
            array(
                'libelle' => 'ZiSécu',
                'boquette' => 'cdf',
                'niveau' => 1,
                'active' => true,
            ),
            array(
                'libelle' => 'ZiAsso',
                'boquette' => 'asso',
                'niveau' => 0,
                'active' => true,
            ),
            array(
                'libelle' => 'ZiCom',
                'boquette' => 'asso',
                'niveau' => 1,
                'active' => true,
            ),
        );

        foreach ($objects as $object) {
            $this->loadResponsabilite(
                $manager,
                $object['libelle'],
                $object['boquette'],
                $object['niveau'],
                $object['active']
            );
        }

        $manager->flush();
    }

    private function loadResponsabilite(ObjectManager $manager, $libelle, $boquette, $niveau, $active)
    {
        $responsabilite = new Responsabilite();
        $responsabilite->setLibelle($libelle);
        $responsabilite->setBoquette($this->getBoquette($boquette));
        $responsabilite->setNiveau($niveau);
        $responsabilite->setActive($active);

        $manager->persist($responsabilite);
        $this->addReference($libelle.'-'.$boquette.'-responsabilite', $responsabilite);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
