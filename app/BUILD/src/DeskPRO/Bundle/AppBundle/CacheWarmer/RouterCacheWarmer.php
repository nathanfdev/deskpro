<?php

namespace DeskPRO\Bundle\AppBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class RouterCacheWarmer
 *
 * @package DeskPRO\Bundle\AppBundle\CacheWarmer
 */
class RouterCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * RouterCacheWarmer constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

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
        $this->router->warmUp($cacheDir);
    }
}
