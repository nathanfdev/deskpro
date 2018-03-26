<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PortalBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return __DIR__;
    }
}
