<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class NotificationCompilerPass.
 */
class NotificationCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $actionAlertHandler = $container->getDefinition('deskpro.notification.notify_handler.action_alert');

        foreach ($container->findTaggedServiceIds('deskpro.notification.action_alert.generator') as $id => $tags) {
            $actionAlertHandler->addMethodCall('attachGenerator', [new Reference($id)]);
        }

        $userNotifyHandler = $container->getDefinition('deskpro.notification.notify_handler.user_notify');

        foreach ($container->findTaggedServiceIds('deskpro.notification.user_notify.generator') as $id => $tags) {
            $userNotifyHandler->addMethodCall('attachGenerator', [new Reference($id)]);
        }
    }
}
