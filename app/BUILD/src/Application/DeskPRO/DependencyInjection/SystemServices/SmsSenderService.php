<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Sms\DeskPROSmsSender;

class SmsSenderService
{
    /**
     * @param DeskproContainer $container
     * @param array            $options
     *
     * @return \Application\DeskPRO\Sms\DeskPROSmsSender
     */
    public static function create(DeskproContainer $container, $options = [])
    {
        $sms_sender = new DeskPROSmsSender(
            null,
            null,
            $container->getJobQueue(),
            $container->getSettingsHandler()->get('core.max_sms_chunks')
        );

        return $sms_sender;
    }
}
