<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\ReportBundle\Dpql2\Func\DpqlFunctionInterface;
use DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder\DpqlPlaceholderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DpqlCompilerPass.
 */
class DpqlCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('dpql.func_registry')) {
            foreach ($container->findTaggedServiceIds('dpql.func') as $id => $attributes) {
                $class      = $container->getDefinition($id)->getClass();
                $reflection = new \ReflectionClass($class);
                if (!$reflection->implementsInterface(DpqlFunctionInterface::class)) {
                    throw new \Exception('DPQL function should implement DpqlFunctionInterface');
                }

                $func     = call_user_func([$class, 'getName']);
                $registry = $container->getDefinition('dpql.func_registry');

                $registry->addMethodCall('addFunction', [$func, $id]);
                $registry->addMethodCall('addFunction', [str_replace('_', '', $func), $id]);
            }
        }

        if ($container->hasDefinition('dpql.placeholder_registry')) {
            foreach ($container->findTaggedServiceIds('dpql.placeholder') as $id => $attributes) {
                $class      = $container->getDefinition($id)->getClass();
                $reflection = new \ReflectionClass($class);
                if (!$reflection->implementsInterface(DpqlPlaceholderInterface::class)) {
                    throw new \Exception('DPQL function should implement DpqlPlaceholderInterface');
                }

                $func     = call_user_func([$class, 'getName']);
                $registry = $container->getDefinition('dpql.placeholder_registry');

                $registry->addMethodCall('addPlaceholder', [$func, $id]);
            }
        }
    }
}
