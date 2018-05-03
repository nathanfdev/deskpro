<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\ExpressionLanguage\Expression;

class AppSecretPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $exp = new Expression("service('app_secret').getAppSecret()");

        foreach ($container->getDefinitions() as $def) {
            foreach ($def->getArguments() as $arg_num => $argument) {
                if ('%kernel.secret%' === $argument || '%secret%' === $argument) {
                    $def->replaceArgument($arg_num, $exp);
                }
            }
        }
    }
}
