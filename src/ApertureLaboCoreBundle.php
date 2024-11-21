<?php

namespace ApertureLabo\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApertureLaboCoreBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}