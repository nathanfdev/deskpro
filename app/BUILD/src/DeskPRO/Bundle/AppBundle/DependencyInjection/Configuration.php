<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('app');

        $rootNode
            ->children()
                ->arrayNode('notification')
                    ->children()
                        ->arrayNode('strategies')->isRequired()
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('strategy')->defaultValue('immediate')->end()
                                    ->arrayNode('delivery')->isRequired()->requiresAtLeastOneElement()
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->scalarNode('persistance')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
