<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection;

use Application\DeskPRO\Elastica\Client;
use Application\DeskPRO\Elastica\IndexFactory;
use Elastica\Index;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ElasticaClientPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.client.default')) {
            return;
        }

        $definition = $container->getDefinition('fos_elastica.client.default');
        $definition->setClass(Client::class);
        $definition->setFactory([new Reference('deskpro.elastica.client_factory'), 'createSystemClientByConfig']);

        $indexFactoryDef = new Definition(IndexFactory::class);
        $indexFactoryDef->setArguments([new Reference('fos_elastica.client.default')]);
        $container->setDefinition('deskpro.elastica.default_index_factory', $indexFactoryDef);

        $indexDef = new Definition(Index::class);
        $indexDef->setFactory([new Reference('deskpro.elastica.default_index_factory'), 'getIndex']);
        $indexDef->setArguments(['deskpro']);
        $container->setDefinition('fos_elastica.index.deskpro', $indexDef);
    }
}
