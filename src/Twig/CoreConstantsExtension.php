<?php

// src/Twig/ConstantsExtension.php

namespace App\Twig;

use ApertureLabo\CoreBundle\Constant\CoreConstants;
use ApertureLabo\CoreBundle\CoreBundle;
use Symfony\Component\DependencyInjection\Extension\AbstractExtension as ExtensionAbstractExtension;
use Twig\Extension\AbstractExtension;

class CoreConstantsExtension extends ExtensionAbstractExtension
{
    public function getGlobals(): array
    {
        return [
            'CORE' => [
                'BUNDLE_NAME' => CoreConstants::BUNDLE_NAME,
                'FONTAWESOME_FREE_6_4_0_JS' => CoreConstants::FONTAWESOME_FREE_6_4_0_JS,
                'JQUERY_3_6_4_JS' => CoreConstants::JQUERY_3_6_4_JS,
            ]
        ];
    }
}
