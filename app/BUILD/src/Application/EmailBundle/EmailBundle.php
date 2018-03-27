<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle;

use Application\EmailBundle\DependencyInjection\Compiler\TwigEnvironmentPass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EmailBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TwigEnvironmentPass());
    }

    /**
     * @param Application $application An Application instance
     */
    public function registerCommands(Application $application)
    {
        $commands = [
            'Application\\EmailBundle\\Command\\CleanSendmailSourcesCommand',
            'Application\\EmailBundle\\Command\\SendSourceCommand',
            'Application\\EmailBundle\\Command\\GenTestEmailCommand',
            'Application\\EmailBundle\\Command\\ProcessQueueCommand',
            'Application\\EmailBundle\\Command\\QueueRawEmailCommand',
            'Application\\EmailBundle\\Command\\GenTestIncomingEmailCommand',
        ];

        foreach ($commands as $cmd) {
            $application->add(new $cmd());
        }
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
