<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\ExpressionLanguage\Expression;

class AppSecretPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $exp = new Expression("service('app_secret').getAppSecret()");

        foreach ($container->getDefinitions() as $service_id => $def) {
            if ('security.authentication.rememberme.services.simplehash.portal' === $service_id) {
                $def->replaceArgument(1, $exp);
            }

            if ('security.authentication.provider.rememberme.portal' === $service_id) {
                $def->replaceArgument(1, $exp);
            }

            if ('security.authentication.provider.anonymous.portal' === $service_id) {
                $def->replaceArgument(0, $exp);
            }

            if ('security.authentication.listener.anonymous.portal' === $service_id) {
                $def->replaceArgument(1, $exp);
            }

            foreach ($def->getArguments() as $arg_num => $argument) {
                if ('%kernel.secret%' === $argument || '%secret%' === $argument) {
                    $def->replaceArgument($arg_num, $exp);
                }
            }
        }
    }
}
