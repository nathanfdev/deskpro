<?php

namespace DeskPRO\Bundle\ReportBundle;

use DeskPRO\Bundle\ReportBundle\DependencyInjection\Compiler\DpqlCompilerPass;
use DeskPRO\Bundle\ReportBundle\DependencyInjection\Compiler\RendererCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ReportBundle.
 */
class ReportBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DpqlCompilerPass());
        $container->addCompilerPass(new RendererCompilerPass());
    }
}
