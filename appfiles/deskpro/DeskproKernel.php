<?php

require_once DP_ROOT.'/autoload.php';

use Symfony\Foundation\Kernel;
use Symfony\Components\DependencyInjection\Loader\YamlFileLoader as ContainerLoader;
use Symfony\Components\Routing\Loader\YamlFileLoader as RoutingLoader;

use Symfony\Foundation\Bundle\KernelBundle;
use Symfony\Framework\FoundationBundle\FoundationBundle;
use Symfony\Framework\ZendBundle\ZendBundle;
use Symfony\Framework\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Framework\DoctrineBundle\DoctrineBundle;
use Symfony\Framework\DoctrineMigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Framework\DoctrineMongoDBBundle\DoctrineMongoDBBundle;
use Symfony\Framework\PropelBundle\PropelBundle;
use Symfony\Framework\TwigBundle\TwigBundle;
use Application\Deskpro;

class DeskproKernel extends Kernel
{
    public function registerRootDir()
    {
        return DP_ROOT.'/deskpro';
    }

    public function registerBundles()
    {
        $bundles = array(
            new KernelBundle(),
            new FoundationBundle(),
            new ZendBundle(),
            new SwiftmailerBundle(),
            new DoctrineBundle(),
            new TwigBundle(),
            new User\CoreBundle(),
        );

        if ($this->isDebug()) {
        }

        return $bundles;
    }

    public function registerBundleDirs()
    {
        return array(
            'Application'        => DP_ROOT.'/src/Application',
            'Bundle'             => DP_ROOT.'/src/Bundle',
            'Symfony\\Framework' => DP_ROOT.'/src/vendor/symfony/src/Symfony/Framework',
        );
    }

    public function registerContainerConfiguration()
    {
        $loader = new ContainerLoader($this->getBundleDirs());

        return $loader->load(DP_ROOT.'/deskpro/config/config_'.$this->getEnvironment().'.yml');
    }

    public function registerRoutes()
    {
        $loader = new RoutingLoader($this->getBundleDirs());

        return $loader->load(DP_ROOT.'/deskpro/config/routing.yml');
    }
}
