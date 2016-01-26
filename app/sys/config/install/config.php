<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

if (!defined('DP_ROOT')) {
    exit('No access');
}
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

#####
# NOTE: unlike all other kernels, the InstallKernel does NOT use "config.shared.php" and so it is a standalone
# kernel config. It uses this file and the InstallExtension container extension.
#####

############################################################################
# Parameters
############################################################################

$container->setParameter('file_locator.class', 'DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator');
$container->setParameter('kernel.include_core_classes', false);
$container->setParameter(
    'templating.cache_warmer.template_paths.class',
    'Application\\DeskPRO\\CacheWarmer\\TemplatePathsCacheWarmer'
);
$container->setParameter('doctrine.orm.proxy_dir', '%kernel.cache_dir%/../doctrine-proxies');
$container->setParameter('templating.globals.class', 'Application\\DeskPRO\\Templating\\GlobalVariables');
$container->setParameter('templating.name_parser.class', 'Application\\DeskPRO\\Templating\\TemplateNameParser');
$container->setParameter(
    'templating.cache_warmer.template_paths.class',
    'Application\\DeskPRO\\CacheWarmer\\TemplatePathsCacheWarmer'
);
$container->setParameter('twig.loader.filesystem.class', 'Application\\DeskPRO\\Twig\\Loader\\HybridLoader');
$container->setParameter('twig.class', 'Application\\DeskPRO\\Twig\\Environment');
$container->setParameter(
    'twig.options',
    array(
        'cache'       => '%kernel.cache_dir%/../twig-compiled',
        'charset'     => 'UTF-8',
        'debug'       => '%kernel.debug%',
        'auto_reload' => '%kernel.debug%',
    )
);
$container->setParameter('templating.locator.class', 'Application\\DeskPRO\\Templating\\Loader\\TemplateLocator');
$container->setParameter('templating.engine.twig.class', 'Application\\DeskPRO\\Twig\\TwigEngine');

############################################################################
# Services
############################################################################

// app secret
$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\AppSecret\AppSecret');
$container->setDefinition('app_secret', $definition);

// twig.helpers.deskpro_templating
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Twig\\Extension\\TemplatingExtension');
$definition->setArguments(
    array(
        new Reference('service_container'),
    )
);
$definition->addTag('twig.extension', array());
$container->setDefinition('twig.helpers.deskpro_templating', $definition);

// doctrine.dbal.connection_factory
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\DBAL\\ConnectionFactory');
$definition->setArguments(
    array(
        '%doctrine.dbal.connection_factory.types%',
    )
);
$definition->addMethodCall('setContainer', array(new Reference('service_container')));
$container->setDefinition('doctrine.dbal.connection_factory', $definition);

// deskpro.interface_value
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\InterfaceValue');
$container->setDefinition('deskpro.interface_value', $definition);

// deskpro.service_urls
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Settings\\ServiceUrls');
$definition->addMethodCall('loadPack', array('%kernel.root_dir%/config/service-urls.php'));
$container->setDefinition('deskpro.service_urls', $definition);

$definition = new Definition(
    'Application\\DeskPRO\\Translate\\Loader\\SystemLoader',
    array(array(DP_ROOT.'/languages'))
);
$container->setDefinition('deskpro.core.translate_loader_system', $definition);

$definition = new Definition('Application\\DeskPRO\\Translate\\Loader\\DeskproLoader');
$definition->addMethodCall('setSystemLoader', array(new Reference('deskpro.core.translate_loader_system')));
$container->setDefinition('deskpro.core.translate_loader', $definition);

// Now create the translate object
$definition = new Definition(
    'Application\\DeskPRO\\Translate\\Translate', array(
        new Reference('deskpro.core.translate_loader'),
        new Reference('event_dispatcher'),
    )
);
$container->setDefinition('deskpro.core.translate', $definition);

// copied from ../config.php
// dp_enc
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Encryption\\DpEnc');
$definition->setFactoryClass('Application\\DeskPRO\\Encryption\\StandardEncFactory');
$definition->setFactoryMethod('create');
$definition->setArguments(array(new Reference('service_container')));
$container->setDefinition('dp_enc', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Encryption\\Form\\Type\\DpEncTextType');
$definition->setArguments(array(new Reference('dp_enc')));
$definition->addTag('form.type', array('alias' => 'dp_enc_text'));
$container->setDefinition('dp_enc.form.type.dp_enc_text', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Encryption\\Form\\Type\\DpEncPasswordType');
$definition->setArguments(array(new Reference('dp_enc')));
$definition->addTag('form.type', array('alias' => 'dp_enc_password'));
$container->setDefinition('dp_enc.form.type.dp_enc_password', $definition);

############################################################################
# Framework Configuration
############################################################################

$container->loadFromExtension(
    'framework',
    array(
        'router' => array(
            'resource' => DP_ROOT.'/sys/config/install/routing.php',
        ),
        'secret'     => 'mube224etsmhxky1gvwixc4b',
        'templating' => array(
            'engines'          => array('php'),
            'assets_base_urls' => 'http://bogus/CONFIG_HTTP',
        ),
        'validation' => array('enabled' => true),
        'form'       => array('enabled' => true),
    )
);

// Monolog default logging, turn off unless specifically enabled (eg in some _dev configs)
$container->loadFromExtension(
    'monolog',
    array(
        'handlers' => array(
            'main' => array(
                'type' => 'null',
            ),
            'email_log_collector' => array(
                'type'     => 'service',
                'id'       => 'email.log_collector',
                'channels' => array(
                    'dp.email.out.mailer',
                    'dp.email.out.transport',
                    'dp.email.out.queue',
                    'dp.email.out.raw_transport',
                ),
            ),
        ),
    )
);

############################################################################
# Doctrine Configuration
############################################################################

$container->loadFromExtension(
    'doctrine',
    array(
        'orm' => array(
            'auto_generate_proxy_classes' => false,
            'default_entity_manager'      => 'default',
            'entity_managers'             => array(
                'default' => array(
                    'mappings' => array(
                        'DeskPRO' => array(
                            'type' => 'staticphp',
                        ),
                        'EmailBundle' => array(
                            'type' => 'staticphp',
                        ),
                        'AppBundle' => array(
                            'type'      => 'annotation',
                            'alias'     => 'App',
                            'is_bundle' => false,
                            'dir'       => '%kernel.root_dir%/../src/DeskPRO/Bundle/AppBundle/Entity',
                            'prefix'    => 'DeskPRO\Bundle\AppBundle\Entity',
                        ),
                    ),
                ),
            ),
        ),
        'dbal' => array(
            'default_connection' => 'default',
            'connections'        => array(
                'default' => array('host' => 'from_user_config.db', 'logging' => true),
                'read'    => array('host' => 'from_user_config.db_read', 'logging' => true),
            ),
            'types' => array(
                'term_engine_term' => 'DeskPRO\Bundle\AppBundle\Doctrine\Type\TermEngineTermType',
            ),
        ),
    )
);

############################################################################
# DeskPRO Configuration
############################################################################

$container->loadFromExtension(
    'install',
    array()
);
