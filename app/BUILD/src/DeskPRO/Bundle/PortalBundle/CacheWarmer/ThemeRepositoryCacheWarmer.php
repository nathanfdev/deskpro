<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\CacheWarmer;

use DeskPRO\Bundle\PortalBundle\Theme\ThemeRepository;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * During cache warming, request all themes from the repo. This will establish the cache.
 */
class ThemeRepositoryCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var ThemeRepository
     */
    private $theme_repository;

    public function __construct(ThemeRepository $theme_repository)
    {
        $this->theme_repository = $theme_repository;
    }

    public function isOptional()
    {
        return false;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        // this method will run through and create the map/cache it for us, we just need to call it to warm it.
        $this->theme_repository->findAll();
    }
}
