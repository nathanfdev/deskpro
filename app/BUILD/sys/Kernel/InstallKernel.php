<?php

/**
 * DeskPRO.
 */

namespace DpSys\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;

class InstallKernel extends BaseKernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \DeskPRO\Bundle\InstallBundle\InstallBundle(),
        ];

        if ('dev' === $this->getEnvironment() || 'test' === $this->getEnvironment()) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(DP_ROOT.'/sys/config/install/install_config_'.$this->getEnvironment().'.yml');
    }
}
