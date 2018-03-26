<?php

namespace DeskPRO\Bundle\AppStoreBundle;

use DeskPRO\Bundle\AppStoreBundle\DependencyInjection\AppStoreExtension;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\InstallAppCommand;
use Symfony\Component\Console;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppStoreBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new AppStoreExtension();
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return __DIR__;
    }

    public function registerCommands(Console\Application $application)
    {
        $application->add(new InstallAppCommand());
    }
}
