<?php

namespace PJM\AppBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\AppBundle\Entity\Event\Evenement;
use PJM\AppBundle\Entity\Item;

class LoadItemData extends BaseFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $items = array(
            array(
                'libelle' => 'Panier de fruits et légumes',
                'slug' => 'paniers',
                'prix' => '300',
                'infos' => array('1 botte de radis roses', '1 salade batavia', '1 oignon jaune sec', '4 tomates rondes', '2 kiwis ', '2 pommes goldrush'),
                'boquette' => 'paniers',
                'valid' => true,
            ),
            array(
                'libelle' => 'Baguette de pain',
                'slug' => 'baguette',
                'prix' => '56',
                'infos' => null,
                'boquette' => 'brags',
                'valid' => false,
            ),
            array(
                'libelle' => 'Baguette de pain',
                'slug' => 'baguette',
                'prix' => '95',
                'infos' => null,
                'boquette' => 'brags',
                'valid' => true,
            ),
            array(
                'libelle' => 'Saucisson',
                'slug' => '',
                'prix' => '2',
                'infos' => null,
                'boquette' => 'cvis',
                'valid' => true,
            ),
            array(
                'libelle' => 'Cheese Burger',
                'slug' => '',
                'prix' => '150',
                'infos' => null,
                'boquette' => 'cvis',
                'valid' => true,
            ),
            array(
                'libelle' => 'Brioche',
                'slug' => '',
                'prix' => '100',
                'infos' => null,
                'boquette' => 'cvis',
                'valid' => true,
            ),
            array(
                'libelle' => 'Petits pois',
                'slug' => '',
                'prix' => '159',
                'infos' => null,
                'boquette' => 'cvis',
                'valid' => true,
            ),
            array(
                'libelle' => 'Chouffe',
                'slug' => '',
                'prix' => '147',
                'infos' => null,
                'boquette' => 'pians',
                'valid' => true,
            ),
            array(
                'libelle' => 'Corsendonk',
                'slug' => '',
                'prix' => '149',
                'infos' => null,
                'boquette' => 'pians',
                'valid' => true,
            ),
        );

        foreach ($items as $item) {
            $this->loadItem(
                $manager,
                $item['libelle'],
                $item['slug'],
                $item['prix'],
                $item['infos'],
                $item['boquette'],
                $item['valid']
            );
        }

        $manager->flush();
    }

    private function loadItem(ObjectManager $manager, $libelle, $slug, $prix, $infos, $boquette, $valid)
    {
        $item = new Item();
        $item->setLibelle($libelle);
        $item->setSlug($slug);

        $item->setPrix($prix);
        $item->setInfos($infos);
        $item->setBoquette($this->getBoquette($boquette));
        $item->setValid($valid);
        $item->setDate($valid ? $this->getRandomDateAgo(0, 4) : $this->getRandomDateAgo(5, 30));

        $manager->persist($item);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
