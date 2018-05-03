<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\DependencyInjection;

use Application\EmailBundle\DependencyInjection\Configuration\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EmailExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('email_settings.yml');
        $loader->load('email_templating.yml');

        $container->setAlias('templating.email', $config['templating']['service']);

        // add paths to the templating.email twig env
        $twigFilesystemLoaderDefinition = $container->getDefinition('templating.email.twig.loader');
        // register bundles as Twig namespaces
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            if (is_dir($dir = $container->getParameter('kernel.root_dir').'/Resources/'.$bundle.'/views')) {
                $this->addTwigPath($twigFilesystemLoaderDefinition, $dir, $bundle);
            }

            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/views')) {
                $this->addTwigPath($twigFilesystemLoaderDefinition, $dir, $bundle);
            }
        }

        // extensions
    }

    private function addTwigPath($twigFilesystemLoaderDefinition, $dir, $bundle)
    {
        $name = $bundle;
        if ('Bundle' === substr($name, -6)) {
            $name = substr($name, 0, -6);
        }
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$dir, $name]);
    }
}
