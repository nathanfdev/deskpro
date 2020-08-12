<?php

namespace DeskPRO\Bundle\AppBundle\CacheWarmer;

use DpRun\DpEnv;
use DpSys\Kernel\ApiKernel;
use DpSys\Kernel\BaseKernel;
use DpSys\Kernel\DpKernel;
use DpSys\Kernel\MessengerKernel;
use DpSys\Kernel\PortalKernel;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Class KernelsCacheWarmer
 *
 * @package DeskPRO\Bundle\AppBundle\CacheWarmer
 */
class KernelsCacheWarmer implements CacheWarmerInterface
{
    /**
     * Collection of kernel classes to boot
     */
    const KERNEL_CLASSES = [
        DpKernel::class,
        ApiKernel::class,
        PortalKernel::class,
        MessengerKernel::class,
    ];

    /**
     * {@inheritDoc}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function warmUp($cacheDir)
    {
        $env = $this->getEnv();

        foreach (self::KERNEL_CLASSES as $kernelClass) {
            /** @var BaseKernel $kernel */
            $kernel = new $kernelClass($env->getEnvId(), $env->isDebug(), $env);
            $kernel->boot();

            /** @var Router $router */
            $router = $kernel->getContainer()->get('router');
            $router->warmUp($cacheDir);
        }
    }

    /**
     * @return DpEnv
     */
    private function getEnv()
    {
        return new DpEnv(DP_DIR);
    }
}
