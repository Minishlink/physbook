<?php

namespace PJM\AppBundle\Security;

use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Services\ResponsableManager;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;

class BoquetteVoter extends AbstractVoter
{
    private $responsableManager;

    public function __construct(ResponsableManager $responsableManager)
    {
        $this->responsableManager = $responsableManager;
    }

    const MANAGE = 'manage';

    protected function getSupportedAttributes()
    {
        return array(self::MANAGE);
    }

    protected function getSupportedClasses()
    {
        return array('PJM\AppBundle\Entity\Boquette');
    }

    /**
     * @param string $attribute
     * @param Boquette $boquette
     * @param User $user
     *
     * @return bool
     */
    protected function isGranted($attribute, $boquette, $user = null)
    {
        switch ($attribute) {
            case self::MANAGE:
                if ($this->responsableManager->estNiveauUn($user, $boquette)) {
                    return true;
                }
                break;
        }

        return false;
    }
}