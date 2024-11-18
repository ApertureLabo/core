<?php

namespace ApertureLabo\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}