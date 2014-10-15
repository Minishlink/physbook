<?php

namespace PJM\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PJMUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
