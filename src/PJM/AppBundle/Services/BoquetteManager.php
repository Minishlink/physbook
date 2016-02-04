<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Boquette;

class BoquetteManager
{
    private $em;
    private static $specialBoquettes;

    public function __construct(EntityManager $em, $specialBoquettes)
    {
        $this->em = $em;
        self::$specialBoquettes = $specialBoquettes;
    }

    /**
     * Get all boquettes
     * @param bool $withSpecial If true, includes special boquettes (pians, cvis, brags, paniers)
     *
     * @return array|Boquette[]
     */
    public function getAll($withSpecial = true)
    {
        return $withSpecial ?
            $this->getRepository()->findAll() :
            $this->getRepository()->getAllExceptSlugs(self::getSpecialBoquettesSlugs());
    }

    public function isSpecialBoquette(Boquette $boquette)
    {
        return in_array($boquette->getSlug(), self::getSpecialBoquettesSlugs());
    }

    public function getType(Boquette $boquette) {
        foreach(self::$specialBoquettes as $type => $specialBoquette) {
            if ($specialBoquette && ($boquette->getSlug() === $specialBoquette['slug'])) {
                return $type;
            }
        }

        return false;
    }

    /**
     * @param string $type bar/epicerie/boulangerie/paniers/journal/bde/bds
     *
     * @return Boquette
     */
    public function getByType($type)
    {
        if (array_key_exists($type, self::$specialBoquettes)) {
            $boquette = self::$specialBoquettes[$type];

            if ($boquette) {
                return $this->getRepository()->findOneBy(array('slug' => $boquette['slug']));
            }
        }

        return null;
    }

    private static function getSpecialBoquettesSlugs()
    {
        $slugs = array();

        foreach (self::$specialBoquettes as $specialBoquette) {
            if ($specialBoquette && array_key_exists('slug', $specialBoquette)) {
                $slugs[] = $specialBoquette['slug'];
            }
        }

        return $slugs;
    }

    private function getRepository()
    {
        return $this->em->getRepository('PJMAppBundle:Boquette');
    }
}
