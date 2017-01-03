<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
