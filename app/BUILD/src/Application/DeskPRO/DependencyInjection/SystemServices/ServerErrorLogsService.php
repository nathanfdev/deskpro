<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\ServerErrorLogs\ServerErrorLogs;

class ServerErrorLogsService
{
    public static function create(DeskproContainer $container)
    {
        return new ServerErrorLogs();
    }
}
