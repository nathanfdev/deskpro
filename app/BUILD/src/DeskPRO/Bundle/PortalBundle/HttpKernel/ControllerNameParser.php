<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\HttpKernel;

use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\Cache\ConvenientCache;
use DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher;
use DeskPRO\Bundle\BrandBundle\Brand\BrandContainer;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\BrandBundle\Theme\PortalBrandThemeLoader;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser as BaseParser;
use Symfony\Component\HttpKernel\KernelInterface;

class ControllerNameParser extends BaseParser
{
    /**
     * @var ArbitraryHasher
     */
    protected $hash_generator;

    /**
     * @var ConvenientCache
     */
    protected $cache;

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var PortalBrandThemeLoader
     */
    private $brand_theme_loader;

    public function __construct(KernelInterface $kernel, BrandStack $brand_stack, PortalBrandThemeLoader $brand_theme_loader)
    {
        parent::__construct($kernel);
        $this->brand_stack        = $brand_stack;
        $this->brand_theme_loader = $brand_theme_loader;
    }

    /**
     * @return BrandStack
     */
    private function getBrandStack()
    {
        return $this->brand_stack;
    }

    public function parse($controller)
    {
        if (!$brand_container = $this->getBrandStack()->getActive()) {
            throw new \RuntimeException('no brand is active in the brand stack. cannot parse theme controller.');
        }

        // if the controller is a string, we're going to array cache the resolved controller based on brand to avoid over-computing
        if (is_string($controller)) {
            return $this->generateAndCache(
                [
                    'parse',
                    $brand_container->getBrand()->getId(),
                    $controller,
                ],
                [$this, 'doParse'],
                [$brand_container, $controller]
            );
        }

        // if it isn't a string, just do the normal work
        return $this->doParse($brand_container, $controller);
    }

    public function doParse(BrandContainer $brand_container, $controller)
    {
        $brand       = $brand_container->getBrand();
        $brand_theme = $this->brand_theme_loader->getPortalBrandTheme($brand);

        if ($theme_controller = $brand_theme->resolveController($controller)) {
            return $theme_controller;
        }

        return parent::parse($controller);
    }

    /**
     * @param mixed $params   the "ArbitraryHasher" input to create cache key for this callable
     * @param mixed $callable doesn't need to be a callable, can be any default value, but usually is a callable
     *
     * @return mixed|null
     */
    protected function generateAndCache($params, $callable, array $args = [])
    {
        return $this->getCache()->get($this->generateHash($params), $callable, $args);
    }

    /**
     * @return ConvenientCache
     */
    protected function getCache()
    {
        if (null === $this->cache) {
            $this->cache = new ConvenientCache(new SimpleArrayCache());
        }

        return $this->cache;
    }

    /**
     * @param mixed $input
     *
     * @return string
     */
    protected function generateHash($input)
    {
        if (null === $this->hash_generator) {
            $this->hash_generator = new ArbitraryHasher();
        }

        return $this->hash_generator->generateHash($input);
    }
}
