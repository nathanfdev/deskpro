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

/* @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

############################################################################
# Parameters
############################################################################

$container->setParameter('twig.loader.filesystem.class', 'Application\\DeskPRO\\Twig\\Loader\\HybridLoader');
$container->setParameter('twig.class', 'Application\\DeskPRO\\Twig\\Environment');
$container->setParameter('templating.name_parser.class', 'Application\\DeskPRO\\Templating\\TemplateNameParser');
$container->setParameter('templating.cache_warmer.template_paths.class', 'Application\\DeskPRO\\CacheWarmer\\TemplatePathsCacheWarmer');
$container->setParameter('templating.locator.class', 'Application\\DeskPRO\\Templating\\Loader\\TemplateLocator');
$container->setParameter('templating.engine.twig.class', 'Application\\DeskPRO\\Twig\\TwigEngine');
$container->setParameter('debug.templating.engine.twig.class', 'Application\\DeskPRO\\Twig\\TwigEngine');
$container->setParameter('twig.cache_warmer.class', 'Application\\DeskPRO\\Twig\\CacheWarmer\\TemplateCacheCacheWarmer');
$container->setParameter('templating.engine.delegating.class', 'Application\\DeskPRO\\Templating\\Engine');

############################################################################
# Services
############################################################################

// templating.engine.jsonphp
$definition = new Definition();
$definition->setClass('Orb\\Templating\\Engine\\PhpVarJsonEngine');
$definition->setArguments(
    array(
        new Reference('templating.name_parser'),
        new Reference('service_container'),
        new Reference('templating.loader'),
        new Reference('templating.globals'),
    )
);
$definition->addTag('templating.engine', array('alias' => 'jsonphp'));
$container->setDefinition('templating.engine.jsonphp', $definition);

// twig.helpers.deskpro_user_templating
$definition = new Definition();
$definition->setClass('Application\\UserBundle\\Twig\\Extension\\UserTemplatingExtension');
$definition->setArguments(array(
    new Reference('service_container'),
));
$definition->addTag('twig.extension', array());
$container->setDefinition('twig.helpers.deskpro_user_templating', $definition);

############################################################################
# Twig Configuration
############################################################################

$container->loadFromExtension('twig', array(
    'form' => array(
        'resources' => array(
            'DeskPRO:Form:form_div_layout.html.twig',
        ),
    ),
));
