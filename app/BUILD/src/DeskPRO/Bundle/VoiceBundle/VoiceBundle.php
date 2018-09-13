<?php

namespace DeskPRO\Bundle\VoiceBundle;

use DeskPRO\Bundle\VoiceBundle\DependencyInjection\Compiler\EventListenerCompilerPass;
use DeskPRO\Bundle\VoiceBundle\DependencyInjection\Compiler\TaskRouterWorkflowsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class VoiceBundle.
 */
class VoiceBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EventListenerCompilerPass());
        $container->addCompilerPass(new TaskRouterWorkflowsCompilerPass());
    }
}
