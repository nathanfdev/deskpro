<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\CacheWarmer;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * During cache warming, all twig templates from the themes are loaded (twig will cache them).
 */
class ThemeTemplateCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var \DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver
     */
    private $theme_resolver;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ThemeResolver $theme_resolver, ContainerInterface $container)
    {
        $this->theme_resolver = $theme_resolver;
        $this->container      = $container;
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
        $map = $this->theme_resolver->getThemeTemplateMap();

        foreach ($map as $theme_id => $theme_map) {
            $this->pushThemeOntoBrandStack($theme_id);

            foreach ($theme_map as $tpl_name => $tpl_path) {
                $this->container->get('twig')->loadTemplate($tpl_name);
            }

            $this->container->get('brand_stack')->pop(); // get rid of brand we pushed onto it
        }
    }

    protected function pushThemeOntoBrandStack($theme_id)
    {
        // we have to "fake" brands in the warmer because our twig env. depends on there being a brand (to determine the filesystem theme).
        // we dont want to make actual db entitites so we just mock this here for the purpose of building the container
        static $i = 100;
        // dont start at 1 because it interferes with the default brand id inside of the brand stack during a cache warmup
        $the_brand     = new Brand();
        $the_brand->id = $i++;
        $theme_set     = new ThemeSet();
        $theme_set->setThemeId($theme_id);
        $the_brand->setThemeSet($theme_set);
        $this->container->get('brand_stack')->push($the_brand);
    }
}
