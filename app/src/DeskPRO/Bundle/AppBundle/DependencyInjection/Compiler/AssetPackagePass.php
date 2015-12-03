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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AssetPackagePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //templating.asset.default_package.http
        $def = new Definition('Symfony\Component\Templating\Asset\Package');
        $def->setFactory(array(new Reference('dp.asset_package_factory'), 'createPackageForPath'));
        $def->setArguments(array('web', false));
        $container->setDefinition('templating.asset.default_package.http', $def);

        //templating.asset.default_package.ssl
        $def = new Definition('Symfony\Component\Templating\Asset\Package');
        $def->setFactory(array(new Reference('dp.asset_package_factory'), 'createPackageForPath'));
        $def->setArguments(array('web', false));
        $container->setDefinition('templating.asset.default_package.ssl', $def);

        //templating.asset.package.vendor_assets.http
        $def = new Definition('Symfony\Component\Templating\Asset\Package');
        $def->setFactory(array(new Reference('dp.asset_package_factory'), 'createPackageForPath'));
        $def->setArguments(array('pub/node_modules', false));
        $container->setDefinition('templating.asset.package.vendor_assets.http', $def);

        //templating.asset.package.vendor_assets.ssl
        $def = new Definition('Symfony\Component\Templating\Asset\Package');
        $def->setFactory(array(new Reference('dp.asset_package_factory'), 'createPackageForPath'));
        $def->setArguments(array('pub/node_modules', true));
        $container->setDefinition('templating.asset.package.vendor_assets.ssl', $def);

        //templating.asset.package.app_assets.http
        $def = new Definition('Symfony\Component\Templating\Asset\Package');
        $def->setFactory(array(new Reference('dp.asset_package_factory'), 'createPackageForPath'));
        $def->setArguments(array('pub/build', false));
        $container->setDefinition('templating.asset.package.app_assets.http', $def);

        //templating.asset.package.app_assets.ssl
        $def = new Definition('Symfony\Component\Templating\Asset\Package');
        $def->setFactory(array(new Reference('dp.asset_package_factory'), 'createPackageForPath'));
        $def->setArguments(array('pub/build', true));
        $container->setDefinition('templating.asset.package.app_assets.ssl', $def);
    }
}
