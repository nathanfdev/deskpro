<?php

namespace Application\DeskPRO\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Overrides the default AppVariable to make it compatible with our legacy version.
 */
class AppVariablePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($def = $container->findDefinition('twig.app_variable')) {
            $def->setClass('Application\DeskPRO\Twig\AppVariable');
        }
        if ($def = $container->findDefinition('templating.globals')) {
            $def->setClass('Application\DeskPRO\Templating\GlobalVariables');
        }
    }
}
