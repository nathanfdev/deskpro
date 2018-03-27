<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Make all of them lazy so their requirements get loaded in order.
 * This matters because some services like Twig expect access to some db-related
 * stuff, which means Doctrine needs to be warmed first.
 */
class LazyWarmersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //------------------------------
        // Init our warmer
        //------------------------------

        $def = $container->getDefinition('cache_warmer');

        $args     = $def->getArguments();
        $new_refs = [];

        foreach ($args[0] as $warmerRef) {
            if (!$warmerRef instanceof Reference) {
                throw new \InvalidArgumentException('Only warmer services can be registered, got: '.get_class($warmerRef));
            }

            // we take a string ref, so the service is only created when the warmer gets to it (in order)
            $new_refs[] = (string) $warmerRef;

            // we need to mark the warmer as public or else
            // symfony will optimise it out
            $warmerDef = $container->getDefinition((string) $warmerRef);
            $warmerDef->setPublic(true);
        }

        $def->setArguments([
            new Reference('service_container'),
            $new_refs,
        ]);
    }
}
