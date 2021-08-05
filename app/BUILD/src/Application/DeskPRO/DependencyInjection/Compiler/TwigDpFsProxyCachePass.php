<?php

namespace Application\DeskPRO\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TwigDpFsProxyCachePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // We need to inject our own twig cache adapter as Sf doesn't seem to support config by service ID
        if ($container->has('twig')) {
            $twig = $container->getDefinition('twig');
            $twig->addMethodCall(
                'setCache', [new Reference('deskpro.twig.cache.dpfs_proxy_filecache')]
            );
        }
    }
}
