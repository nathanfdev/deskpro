<?php

namespace DeskPRO\Bundle\SystemBundle;

use DeskPRO\Bundle\SystemBundle\DependencyInjection\Compiler\MonologHandlerCompilerPass;
use DeskPRO\Bundle\SystemBundle\DependencyInjection\Compiler\TriggersCollectorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SystemBundle.
 */
class SystemBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new MonologHandlerCompilerPass());
        $container->addCompilerPass(new TriggersCollectorCompilerPass());
    }
}
