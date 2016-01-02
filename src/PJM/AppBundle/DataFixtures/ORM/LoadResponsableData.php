<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\AppBundle\Entity\Responsable;

class LoadResponsableData extends BaseFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $objects = array(
            array(
                'user' => 'p3',
                'libelle' => 'ZiCdF',
                'boquette' => 'cdf',
                'active' => true,
            ),
            array(
                'user' => 'archi',
                'libelle' => 'ViZiCdF',
                'boquette' => 'cdf',
                'active' => false,
            ),
            array(
                'user' => 'ancienAngers',
                'libelle' => 'ZiSécu',
                'boquette' => 'cdf',
                'active' => true,
            ),
            array(
                'user' => 'ancien',
                'libelle' => 'ZiAsso',
                'boquette' => 'asso',
                'active' => true,
            ),
            array(
                'user' => 'ancienne',
                'libelle' => 'ZiCom',
                'boquette' => 'asso',
                'active' => true,
            ),
        );

        foreach ($objects as $object) {
            $this->loadResponsable(
                $manager,
                $object['user'],
                $object['libelle'],
                $object['boquette'],
                $object['active']
            );
        }

        $manager->flush();
    }

    private function loadResponsable(ObjectManager $manager, $user, $libelle, $boquette, $active)
    {
        $responsable = new Responsable();
        $responsable->setUser($this->getUser($user));
        $responsable->setResponsabilite($this->getResponsabilite($libelle, $boquette));
        $responsable->setActive($active);

        $manager->persist($responsable);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
