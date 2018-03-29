<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class LogoBlobService
{
    public static function create(DeskproContainer $container)
    {
        $blob_id = $container->getSetting('core.deskpro_logo_blob');
        if (!$blob_id) {
            return;
        }

        $blob = $container->getEm()->find('DeskPRO:Blob', $blob_id);

        // If the blob is invalid for some reason, unset the setting
        if (!$blob) {
            $container->getEm()->getRepository('DeskPRO:Setting')->updateSetting('core.deskpro_logo_blob', null);

            return;
        }

        return $blob;
    }
}
