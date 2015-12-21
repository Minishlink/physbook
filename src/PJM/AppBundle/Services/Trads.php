<?php

namespace PJM\AppBundle\Services;

class Trads
{
    /** @var boolean */
    private $exanceEnabled;

    public function __construct($exanceEnabled) {
        $this->exanceEnabled = $exanceEnabled;
    }

    public function getNums($fams) {
        $nums = array_map(function($fam) {
            if ($fam === 'XIII') {
                return 13;
            }

            $len = strlen($fam);

            if ($len > 1 && $fam[$len-1] === '!') {
                return $this->fact((int)substr($fam, 0, $len - 1));
            }

            if ($len > 2 && substr($fam, -3) === 'bis') {
                return (int)$fam + 1;
            }

            return (int)$fam;
        },  preg_split('/[\s:#-]+/', $fams));

        sort($nums);

        return $nums;
    }

    private function fact($n) {
        return $n ? ($n * $this->fact($n-1)) : 1;
    }

    public function getExanceFromDate(\DateTime $date) {
        $dateExanceZero = new \DateTime('first friday of june '.$date->format('Y'));

        if ($date > $dateExanceZero) {
            $dateExanceZero->modify('+1 year');
        }

        return $dateExanceZero->diff($date)->days;
    }

    /**
     * @return boolean
     */
    public function isExanceEnabled()
    {
        return $this->exanceEnabled;
    }
}
