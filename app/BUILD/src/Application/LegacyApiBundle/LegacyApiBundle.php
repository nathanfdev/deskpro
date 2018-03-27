<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle;

use Application\DeskPRO\CustomFields\Form\FormHelper;
use Application\LegacyApiBundle\DependencyInjection\AccessDecisionPass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LegacyApiBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function registerCommands(Application $application)
    {
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->register(FormHelper::class, FormHelper::class)
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument(new Reference('form.factory'))
        ;

        $container->registerExtension(new \Application\LegacyApiBundle\DependencyInjection\CoreExtension());
        $container->addCompilerPass(new AccessDecisionPass());
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return __DIR__;
    }
}
