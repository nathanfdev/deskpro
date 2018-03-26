<?php

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
