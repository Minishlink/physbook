<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\AppBundle\Entity\FeaturedItem;

class LoadFeaturedItem extends BaseFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $featuredItems = array(
            'brioche',
            'chouffe',
        );

        foreach ($featuredItems as $featuredItem) {
            $this->loadItem(
                $manager,
                $featuredItem
            );
        }

        $manager->flush();
    }

    private function loadItem(ObjectManager $manager, $itemSlug)
    {
        $featuredItem = new FeaturedItem();
        $featuredItem->setItem($this->getItem($itemSlug));

        $manager->persist($featuredItem);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
