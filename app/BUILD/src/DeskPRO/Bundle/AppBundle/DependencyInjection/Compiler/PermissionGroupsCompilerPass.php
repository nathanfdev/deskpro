<?php

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\PermissionGroupEntityVoterInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class PermissionGroupsCompilerPass.
 */
class PermissionGroupsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('security.voter.permission_groups')) {
            return;
        }

        $entityVoters = [];
        foreach ($container->findTaggedServiceIds('security.voter.permission_groups') as $id => $attributes) {
            $class = $container->getDefinition($id)->getClass();
            $ref   = new \ReflectionClass($class);

            if (!$ref->implementsInterface(PermissionGroupEntityVoterInterface::class)) {
                throw new \RuntimeException('Expected instance of '.PermissionGroupEntityVoterInterface::class);
            }

            $entityVoters[call_user_func([$class, 'getEntityClass'])] = $id;
        }

        $container->getDefinition('security.voter.permission_groups')->addMethodCall('setEntityVoters', [$entityVoters]);
    }
}
