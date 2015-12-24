<?php

namespace PJM\AppBundle\Security;

use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Services\BoquetteManager;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;

class BoquetteVoter extends AbstractVoter
{
    private $boquetteManager;

    public function __construct(BoquetteManager $boquetteManager)
    {
        $this->boquetteManager = $boquetteManager;
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
                if ($this->boquetteManager->canManage($user, $boquette)) {
                    return true;
                }
                break;
        }

        return false;
    }
}