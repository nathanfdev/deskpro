<?php

namespace DpTestSrc\TestBundle;

use DpTestSrc\TestBundle\DependencyInjection\Compiler\DisableAuditLogCompilerPass;
use DpTestSrc\TestBundle\DependencyInjection\Compiler\DisableSecureCookieCompilerPass;
use DpTestSrc\TestBundle\DependencyInjection\TestExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class TestBundle.
 */
class TestBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new TestExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DisableAuditLogCompilerPass());
        $container->addCompilerPass(new DisableSecureCookieCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function registerCommands(Application $application)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return __DIR__;
    }
}
