<?php

namespace DpTestSrc\TestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DisableSecureCookieCompilerPass.
 */
class DisableSecureCookieCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // disable 'cookie_secure' settings in tests
        $options = $container->getParameter('session.storage.options');
        if ($options && isset($options['cookie_secure']) && $options['cookie_secure']) {
            $options['cookie_secure'] = false;

            $container->setParameter('session.storage.options', $options);
        }
    }
}
