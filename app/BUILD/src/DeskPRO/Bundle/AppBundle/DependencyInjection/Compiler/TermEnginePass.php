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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Wires together the different term engines.
 */
class TermEnginePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->addDbalTicketFilterTermCompilers($container);
        $this->addDbalTermCompilerHelpers($container);
        $this->addDbalTicketFilterVisitors($container);

        $this->addPhpTicketCheckerTermCompilers($container);
        $this->addPhpTermCompilerHelpers($container);
        $this->addPhpTicketCheckerVisitors($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addDbalTicketFilterTermCompilers(ContainerBuilder $container)
    {
        $compiler_factory_def = $container->findDefinition('term_engine.dbal_ticket_filters.compiler.factory');

        $tagged_compilers = $container->findTaggedServiceIds('dbal_ticket_filter_compiler');

        $compiler_array = [];

        foreach ($tagged_compilers as $id => $tags) {
            foreach ($tags as $attributes) {
                $compiler_array[$attributes['term']] = new Reference($id);
            }
        }

        $compiler_factory_def->setArguments([$compiler_array]);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addPhpTicketCheckerTermCompilers(ContainerBuilder $container)
    {
        $compiler_factory_def = $container->findDefinition('term_engine.php_ticket_checker.compiler.factory');

        $tagged_compilers = $container->findTaggedServiceIds('php_ticket_checker_compiler');

        $compiler_array = [];

        foreach ($tagged_compilers as $id => $tags) {
            foreach ($tags as $attributes) {
                $compiler_array[$attributes['term']] = new Reference($id);
            }
        }

        $compiler_factory_def->setArguments([$compiler_array]);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addDbalTicketFilterVisitors(ContainerBuilder $container)
    {
        $compiler_def = $container->findDefinition('term_engine.dbal_ticket_filters.compiler');

        $tagged_visitors = $container->findTaggedServiceIds('dbal_ticket_filter_visitor');

        $visitors = [];

        foreach ($tagged_visitors as $id => $tags) {
            $visitors[] = new Reference($id);
        }

        $compiler_def->replaceArgument(1, $visitors);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addPhpTicketCheckerVisitors(ContainerBuilder $container)
    {
        $compiler_def = $container->findDefinition('term_engine.php_ticket_checker.compiler');

        $tagged_visitors = $container->findTaggedServiceIds('php_ticket_checker_visitor');

        $visitors = [];

        foreach ($tagged_visitors as $id => $tags) {
            $visitors[] = new Reference($id);
        }

        $compiler_def->replaceArgument(1, $visitors);
    }

    private function addDbalTermCompilerHelpers(ContainerBuilder $container)
    {
        $helper_pool = $container->findDefinition('term_engine.dbal.helper_pool');

        $helper_tags = $container->findTaggedServiceIds('dbal_term_engine_helper');

        $helpers = [];

        foreach ($helper_tags as $id => $tags) {
            $helpers[] = new Reference($id);
        }

        $helper_pool->setArguments([$helpers]);
    }

    private function addPhpTermCompilerHelpers(ContainerBuilder $container)
    {
        $helper_pool = $container->findDefinition('term_engine.php.helper_pool');

        $helper_tags = $container->findTaggedServiceIds('php_term_engine_helper');

        $helpers = [];

        foreach ($helper_tags as $id => $tags) {
            $helpers[] = new Reference($id);
        }

        $helper_pool->setArguments([$helpers]);
    }
}
