<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AuditBundle;

use DeskPRO\Bundle\AuditBundle\DependencyInjection\Compiler\FilterServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AuditBundle.
 */
class AuditBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new FilterServiceCompilerPass());
    }
}
