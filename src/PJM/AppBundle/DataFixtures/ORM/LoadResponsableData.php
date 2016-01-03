<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadResponsableData extends BaseFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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

        $responsableManager = $this->container->get('pjm.services.responsable_manager');

        foreach ($objects as $object) {
            $responsableManager->create(
                $this->getUser($object['user']),
                $this->getResponsabilite($object['libelle'], $object['boquette']),
                $object['active'],
                false
            );
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
