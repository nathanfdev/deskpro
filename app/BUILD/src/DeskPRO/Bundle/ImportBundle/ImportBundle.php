<?php

namespace DeskPRO\Bundle\ImportBundle;

use DeskPRO\Bundle\ImportBundle\DependencyInjection\Compiler\EntityHandlerRegistryCompilerPass;
use DeskPRO\Bundle\ImportBundle\DependencyInjection\Compiler\EventListenerCompilerPass;
use DeskPRO\Bundle\ImportBundle\DependencyInjection\Compiler\HelperRegistryCompilerPass;
use DeskPRO\Bundle\ImportBundle\DependencyInjection\Compiler\MapperRegistryCompilerPass;
use DeskPRO\Bundle\ImportBundle\DependencyInjection\Compiler\SourceScriptResolverCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ImportBundle.
 */
class ImportBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MapperRegistryCompilerPass());
        $container->addCompilerPass(new HelperRegistryCompilerPass());
        $container->addCompilerPass(new EntityHandlerRegistryCompilerPass());
        $container->addCompilerPass(new SourceScriptResolverCompilerPass());
        $container->addCompilerPass(new EventListenerCompilerPass());
    }
}
