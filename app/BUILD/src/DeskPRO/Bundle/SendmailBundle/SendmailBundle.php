<?php

namespace DeskPRO\Bundle\SendmailBundle;

use DeskPRO\Bundle\SendmailBundle\DependencyInjection\Compiler\TwigEnvironmentPass;
use DeskPRO\Bundle\SendmailBundle\DependencyInjection\SendmailExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SendmailBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new SendmailExtension();
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TwigEnvironmentPass());
    }
}
