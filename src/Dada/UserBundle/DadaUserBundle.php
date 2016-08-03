<?php

namespace Dada\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DadaUserBundle extends Bundle
{
    /**
     * Child of FOSUserBundle -> templates are located in this bundle
     * @return string
     */
    public function getParent(){
        return 'FOSUserBundle';
    }
}
