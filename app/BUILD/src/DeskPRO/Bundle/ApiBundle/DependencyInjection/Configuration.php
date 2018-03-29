<?php

namespace DeskPRO\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $notValidVersion = function ($v) {
            return !preg_match('#^\d{8}$#', $v);
        };

        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('api');
        $rootNode
            ->children()
                ->scalarNode('default_version')
                    ->defaultValue('latest')
                    ->validate()
                        ->ifTrue(function ($v) use ($notValidVersion) {
                            return $v !== 'latest' && $notValidVersion($v);
                        })
                        ->thenInvalid('Check api default_version format')
                    ->end()
                ->end()
                ->arrayNode('versions')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                        ->validate()
                            ->ifTrue($notValidVersion)
                            ->thenInvalid('Check api version "%s" format')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
