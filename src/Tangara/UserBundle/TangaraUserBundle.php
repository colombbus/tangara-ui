<?php

namespace Tangara\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TangaraUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
