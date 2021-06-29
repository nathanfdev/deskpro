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

            $providers = [
                'security.authentication.provider.anonymous.default',
                'security.authentication.provider.anonymous.api_anonymous_home',
                'security.authentication.provider.anonymous.api_oauth_anonymous',
                'security.authentication.provider.anonymous.api_oauth_access',
                'security.authentication.provider.anonymous.api_anonymous_usersources',
                'security.authentication.provider.anonymous.api_anonymous_dashboard_view',
                'security.authentication.provider.anonymous.api_anonymous_voice',
                'security.authentication.provider.anonymous.api_anonymous',
                'security.authentication.provider.anonymous.api_messenger_anonymous_home',
                'security.authentication.provider.anonymous.api_messenger_anonymous_setup',
            ];

            $listeners = [
                'security.authentication.listener.anonymous.default',
                'security.authentication.listener.anonymous.api_anonymous_home',
                'security.authentication.listener.anonymous.api_oauth_anonymous',
                'security.authentication.listener.anonymous.api_oauth_access',
                'security.authentication.listener.anonymous.api_anonymous_usersources',
                'security.authentication.listener.anonymous.api_anonymous_dashboard_view',
                'security.authentication.listener.anonymous.api_anonymous_voice',
                'security.authentication.listener.anonymous.api_anonymous',
                'security.authentication.listener.anonymous.api_messenger_anonymous_home',
                'security.authentication.listener.anonymous.api_messenger_anonymous_setup',
                'security.authentication.listener.anonymous.portal',
            ];

            if (in_array($service_id, $providers)) {
                $def->replaceArgument(0, $exp);
            }

            if (in_array($service_id, $listeners)) {
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
