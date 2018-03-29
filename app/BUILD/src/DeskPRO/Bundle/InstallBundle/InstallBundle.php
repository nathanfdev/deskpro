<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\InstallBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class InstallBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
        $application->add(new Command\CleanCommand());
        $application->add(new Command\CheckFileIntegrityCommand());
        $application->add(new Command\InstallCommand());
        $application->add(new Command\InstallFreshConfigCommand());
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
