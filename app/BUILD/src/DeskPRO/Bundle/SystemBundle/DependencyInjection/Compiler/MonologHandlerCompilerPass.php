<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MonologHandlerCompilerPass.
 *
 * Adds the system alerts logging handler to monolog
 */
class MonologHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private static $loggerServiceName = 'dp_sys.alerts.monolog_handler';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::$loggerServiceName)) {
            throw new \Exception('System alerts Monolog handler service not found');
        }
        if (!$logger = $container->findDefinition('monolog.logger')) {
            throw new \Exception('monolog.logger service not found');
        }

        $logger->addMethodCall('pushHandler', [new Reference(self::$loggerServiceName)]);
    }
}
