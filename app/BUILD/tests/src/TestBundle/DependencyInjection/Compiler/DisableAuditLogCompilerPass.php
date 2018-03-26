<?php

namespace DpTestSrc\TestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DisableAuditLogCompilerPass.
 */
class DisableAuditLogCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // disable audit listener on test
        if ($container->hasDefinition('audit_log.doctrine_listener')) {
            $container->getDefinition('audit_log.doctrine_listener')->addMethodCall('disableListener');
        }
    }
}
