<?php

namespace DeskPRO\Bundle\VoiceBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\WorkflowInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class TaskRouterWorkflowsCompilerPass.
 */
class TaskRouterWorkflowsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('dp.voice.task_router')) {
            return;
        }

        $workflows = [];
        foreach ($container->findTaggedServiceIds('dp.voice.task_router_workflow') as $id => $attributes) {
            $class = $container->getDefinition($id)->getClass();
            $ref   = new \ReflectionClass($class);

            if (!$ref->implementsInterface(WorkflowInterface::class)) {
                throw new \RuntimeException('Expected instance of '.WorkflowInterface::class);
            }

            $workflows[call_user_func([$class, 'getChannelName'])] = $id;
        }

        $container->getDefinition('dp.voice.task_router')->addMethodCall('setWorkflows', [$workflows]);
    }
}
