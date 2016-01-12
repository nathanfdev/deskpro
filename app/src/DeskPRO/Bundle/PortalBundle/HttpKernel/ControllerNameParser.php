<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\HttpKernel;

use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\Cache\ConvenientCache;
use DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
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
            $this->getBrandStack()->push($this->getBrandStack()->getDefault());
        }

        if (!$brand_container && !$brand_container = $this->getBrandStack()->getActive()) {
            throw new \RuntimeException('no brand is active in the brand stack. cannot parse theme controller.');
        }

        // if the controller is a string, we're going to array cache the resolved controller based on brand to avoid over-computing
        if (is_string($controller)) {
            return $this->generateAndCache(
                array(
                    'parse',
                    $brand_container->getBrand()->getId(),
                    $controller,
                ),
                array($this, 'doParse'),
                array($brand_container, $controller)
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
    protected function generateAndCache($params, $callable, array $args = array())
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
