<?php

namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * This is a copy of the swiftmailer ext to customize the Configuration class
 * to allow our custom transport
 */
class SwiftmailerExtension extends \Symfony\Bundle\SwiftmailerBundle\DependencyInjection\SwiftmailerExtension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(DP_ROOT.'/vendor/symfony/src/Symfony/Bundle/SwiftmailerBundle/Resources/config'));
        $loader->load('swiftmailer.xml');
        $container->setAlias('mailer', 'swiftmailer.mailer');

        $r = new \ReflectionClass('Swift_Message');
        $container->setParameter('swiftmailer.base_dir', dirname(dirname(dirname($r->getFilename()))));

        $configuration = new SwiftmailerConfiguration();
        $processor = new Processor();
        $config = $processor->process($configuration->getConfigTree($container->getParameter('kernel.debug')), $configs);

		$transport = $config['transport'];
        $container->setParameter('swiftmailer.transport.name', $transport);
        $container->setAlias('swiftmailer.transport', 'swiftmailer.transport.'.$transport);
		$container->setParameter('swiftmailer.single_address', null);
    }

	public function getAlias()
    {
        return 'swiftmailer';
    }
}
