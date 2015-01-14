<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AppBundle\DependencyInjection\Compiler;


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

            if ('dp_security.form_login.listener' === $service_id) {
                $def->replaceArgument(4, $exp);
            }

            foreach ($def->getArguments() as $arg_num => $argument) {
                if ('%kernel.secret%' === $argument || '%secret%' === $argument) {
                    $def->replaceArgument($arg_num, $exp);
                }
            }
        }
    }
}
