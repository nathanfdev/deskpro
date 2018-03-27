<?php

/**
 * DeskPRO.
 */

namespace deskpro_ms_translator\DependencyInjection;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Orb\Service\Microsoft\Translate\Translate;

class MsTranslatorService
{
    public static function create(DeskproContainer $container, AppInstance $app)
    {
        $api = new Translate(
            $app->getSetting('client_id'),
            $app->getSetting('client_secret')
        );

        return $api;
    }
}
