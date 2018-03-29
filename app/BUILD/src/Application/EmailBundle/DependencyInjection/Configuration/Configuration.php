<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('deskpro_email');

        $rootNode
            ->children()
                ->arrayNode('templating')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')->defaultValue('templating.email.engine')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
