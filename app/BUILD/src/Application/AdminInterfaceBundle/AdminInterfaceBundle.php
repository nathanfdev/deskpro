<?php

/**
 * DeskPRO.
 */

namespace Application\AdminInterfaceBundle;

use Symfony\Component\Console\Application;

class AdminInterfaceBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
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
