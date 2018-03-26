<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Encryption;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class StandardEncFactory
{
    public static function create(DeskproContainer $container)
    {
        $is_enabled = $container->getSetting('core.use_encryption');
        $key_file   = $container->get('deskpro.app_env')->findConfigFile('encryption-key.bin') ?: false;

        return new DpEnc($is_enabled, $key_file);
    }
}
