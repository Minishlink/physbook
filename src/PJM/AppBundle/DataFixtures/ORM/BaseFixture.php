<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use joshtronic\LoremIpsum;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Item;
use PJM\AppBundle\Entity\User;

abstract class BaseFixture extends AbstractFixture
{
    private $loremIpsum;

    /**
     * Get User by username
     *
     * @param string $username
     * @return User
     */
    protected function getUser($username)
    {
        return $this->getReference($username."-user");
    }

    /**
     * Get Boquette by slug
     *
     * @param string $slug
     * @return Boquette
     */
    protected function getBoquette($slug)
    {
        return $this->getReference($slug."-boquette");
    }

    /**
     * Get Item by slug and valid
     *
     * @param string $slug
     * @param bool $valid
     * @return Item
     */
    protected function getItem($slug, $valid = true)
    {
        return $this->getReference($slug.($valid ? '-valid' : '').'-item');
    }

    /**
     * @param $min
     * @param $max
     * @return \DateTime
     */
    protected function getRandomDateAgo($min, $max)
    {
        return $this->getRandomDate($min, $max, true);
    }

    /**
     * @param $min
     * @param $max
     * @return \DateTime
     */
    protected function getRandomDateLater($min, $max)
    {
        return $this->getRandomDate($min, $max, false);
    }

    /**
     * @param $min
     * @param $max
     * @param bool $ago
     * @return \DateTime
     */
    protected function getRandomDate($min, $max, $ago)
    {
        $sign = $ago ? '-' : '+';
        $date = new \DateTime($sign.rand($min, $max).' days');
        $date->setTime(rand(0,23), rand(0,59));

        return $date;
    }

    /**
     * @param int $nb
     * @param string $type
     * @return mixed
     */
    protected function getLoremIpsum($nb = 1, $type = 'sentences')
    {
        if (!isset($this->loremIpsum)) {
            $this->loremIpsum = new LoremIpsum();
        }

        switch ($type) {
            case 'words':
                return $this->loremIpsum->words($nb);
            case 'sentences':
                return $this->loremIpsum->sentences($nb);
            case 'paragraphs':
                return $this->loremIpsum->paragraphs($nb);
            default:
                return '';
        }
    }
}
