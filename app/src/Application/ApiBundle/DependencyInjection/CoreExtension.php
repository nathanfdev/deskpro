<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 */

namespace Application\ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Registers basic core stuff
 */
class CoreExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $definition = new Definition('Application\\ApiBundle\\Request\\RequestAuth');
        $definition->setScope('request');
        $definition->setArguments(array(
            new Reference('doctrine.orm.entity_manager'),
            new Reference('request')
        ));
        $container->setDefinition('deskpro.api.request_auth', $definition);

        $definition = new Definition('Application\\DeskPRO\\AuditLog\\AuditManager');
        $definition->setFactoryClass('Application\\DeskPRO\\AuditLog\\AuditManagerFactory');
        $definition->setFactoryMethod('getAuditManager');
        $container->setDefinition('deskpro.auditlog.manager', $definition);

        $definition = new Definition('Application\\DeskPRO\\AuditLog\\AuditDoctrineListener');
        $definition->setFactoryClass('Application\\DeskPRO\\AuditLog\\AuditManagerFactory');
        $definition->setFactoryMethod('getAuditListener');
        $definition->setArguments(array(new Reference('deskpro.auditlog.manager')));
        $definition->addTag('doctrine.event_subscriber');
        $container->setDefinition('deskpro.auditlog.doctrine_listener', $definition);

        $definition = new Definition('Application\\DeskPRO\\AuditLog\\AuditWriter\\AuditDbWriter');
        $definition->setFactoryClass('Application\\DeskPRO\\AuditLog\\AuditManagerFactory');
        $definition->setFactoryMethod('getAuditDbWriter');
        $definition->addTag('deskpro.auditlog.writers');
        $container->setDefinition('deskpro.auditlog.writer.db', $definition);

        $container
            ->register('kernel.listener.controller_post_action', 'Application\\ApiBundle\\Event\\LogApiCallListener')
            ->addTag('kernel.event_listener', array(
                'event' => 'DeskPRO_onControllerPostAction', 'method' => 'onControllerPostAction')
            )
        ;
    }

    public function getXsdValidationBasePath()
    {
        return null;
    }

    public function getNamespace()
    {
        return null;
    }

    public function getAlias()
    {
        return 'deskpro_api_core';
    }
}
