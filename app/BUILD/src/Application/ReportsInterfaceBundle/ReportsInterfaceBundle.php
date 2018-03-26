<?php

/**
 * DeskPRO.
 */

namespace Application\ReportsInterfaceBundle;

use Symfony\Component\Console\Application;

class ReportsInterfaceBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
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
