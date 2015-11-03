<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use joshtronic\LoremIpsum;
use PJM\AppBundle\Entity\User;

abstract class BaseFixture extends AbstractFixture
{
    private $loremIpsum;

    /**
     * @param $user
     * @return User
     */
    protected function getUser($user)
    {
        return $this->getReference($user."-user");
    }

    /**
     * @param $min
     * @param $max
     * @return \DateTime
     */
    protected function getRandomDateAgo($min, $max)
    {
        $date = new \DateTime(rand($min, $max).' days ago');
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
