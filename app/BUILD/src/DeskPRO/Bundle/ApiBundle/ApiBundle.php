<?php

namespace DeskPRO\Bundle\ApiBundle;

use DeskPRO\Bundle\ApiBundle\DependencyInjection\Compiler\ApiDocPass;
use DeskPRO\Bundle\ApiBundle\DependencyInjection\Compiler\NelmioCorsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ApiBundle.
 */
class ApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ApiDocPass());
        $container->addCompilerPass(new NelmioCorsPass());
        parent::build($container);
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
