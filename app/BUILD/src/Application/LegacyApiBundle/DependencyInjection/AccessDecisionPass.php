<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;

class AccessDecisionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('security.access.decision_manager')->setArguments(
            [
                [],
                AccessDecisionManager::STRATEGY_CONSENSUS,
                true,
                true,
            ]
        );
    }
}
