<?php
namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class SwiftmailerConfiguration
{
    public function getConfigTree($kernelDebug)
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('swiftmailer');

        $rootNode
            ->children()
                ->scalarNode('transport')
                    ->defaultValue('smtp')
                    ->validate()
                        ->ifNotInArray(array ('smtp', 'mail', 'sendmail', 'gmail', 'dp_delegating'))
                        ->thenInvalid('The %s transport is not supported')
                    ->end()
                ->end()
                ->scalarNode('username')->defaultNull()->end()
                ->scalarNode('password')->defaultNull()->end()
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->scalarNode('port')->defaultValue(false)->end()
                ->scalarNode('encryption')
                    ->defaultNull()
                    ->validate()
                        ->ifNotInArray(array ('tls', 'ssl', null))
                        ->thenInvalid('The %s encryption is not supported')
                    ->end()
                ->end()
                ->scalarNode('auth_mode')
                    ->defaultNull()
                    ->validate()
                        ->ifNotInArray(array ('plain', 'login', 'cram-md5', null))
                        ->thenInvalid('The %s authentication mode is not supported')
                    ->end()
                ->end()
                ->arrayNode('spool')
                    ->children()
                        ->scalarNode('type')->defaultValue('file')->end()
                        ->scalarNode('path')->defaultValue('%kernel.cache_dir%/swiftmailer/spool')->end()
                    ->end()
                ->end()
                ->scalarNode('delivery_address')->end()
                ->booleanNode('disable_delivery')->end()
                ->booleanNode('logging')->defaultValue($kernelDebug)->end()
            ->end()
        ;

        return $treeBuilder->buildTree();
    }
}
