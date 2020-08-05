<?php

namespace DeskPRO\Bundle\AppBundle\Routing;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\PortalBundle\Routing\PortalRouter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Class UrlRequestContextFactory.
 */
class UrlRequestContextFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Cache of brandId => context for that brand.
     *
     * @var RequestContext
     */
    private $absContextToBrand = [];

    /**
     * A string scheme+host, or empty string if its a local path, or null if disabled.
     *
     * @var string|null
     */
    private $cfImgResizeZone = -1;

    /**
     * UrlContextFactory constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param RequestContext $defaultContext
     * @param string         $name
     * @param array          $parameters
     * @param int            $referenceType
     *
     * @return RequestContext
     */
    public function createGenerateContext(RequestContext $defaultContext, $name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if ($referenceType === UrlGeneratorInterface::ABSOLUTE_URL) {
            return $this->createContextForSettingsUrl($defaultContext, $parameters, $name);
        }

        return $defaultContext;
    }

    /**
     * @param RequestContext $defaultContext
     * @param array $parameters
     * @param string $name
     *
     * @return RequestContext
     */
    private function createContextForSettingsUrl(RequestContext $defaultContext, array $parameters, $name)
    {
        $brandStack = $this->container->get('brand_stack');
        /** @var Brand $brand */
        $brand = array_key_exists('brand', $parameters) && $parameters['brand'] instanceof Brand
            ? $parameters['brand']
            : $brandStack->getActive()->getBrand();

        if (isset($this->absContextToBrand[$brand->getId()][$name])) {
            return $this->absContextToBrand[$brand->getId()][$name];
        }

        $settingsResolver = $this->container->get('settings_resolver');

        $brandUrl  = $settingsResolver->getBrandSettings($brand)->get('core.deskpro_url');
        $globalUrl = $settingsResolver->getGlobalSettings()->get('core.deskpro_url');

        $url = $brandUrl ?: $globalUrl;
        if (!$url) {
            return $defaultContext;
        }

        $urlParts = @parse_url($url);

        if (!$urlParts || empty($urlParts['host']) || empty($urlParts['scheme'])) {
            return $defaultContext;
        }

        $context = clone $defaultContext;
        $context->setHost($urlParts['host']);
        $context->setScheme($urlParts['scheme']);

        $slugPrefix = '/b/'.$brand->getSlug();
        if (!$brandUrl && $brand !== $brandStack->getDefaultBrand() && strpos($context->getBaseUrl(), $slugPrefix) === false
            && !in_array($name, PortalRouter::$nonBrandRoutes)
        ) {
            $context->setBaseUrl(rtrim($context->getBaseUrl(), '/').$slugPrefix);
        }

        $port = (int) @$urlParts['port'];
        if (!$port) {
            if ($urlParts['scheme'] === 'https') {
                $port = 443;
            } else {
                $port = 80;
            }
        }

        if ($urlParts['scheme'] === 'https') {
            $context->setHttpsPort($port);
            $context->setHttpPort(80);
        } else {
            $context->setHttpPort($port);
            $context->setHttpsPort(443);
        }

        $this->absContextToBrand[$brand->getId()][$name] = $context;

        return $context;
    }

    public function hasCfImageResize()
    {
        if ($this->cfImgResizeZone === -1) {
            $this->cfImgResizeZone = rtrim(
                $this->container->get('settings_resolver')->getGlobalSettings()->get('images.cf_resize_zone', ''),
                '/'
            ) ?: null;
        }

        return $this->cfImgResizeZone !== null;
    }

    public function getCfResizeUrl($url, array $cfParams, RequestContext $context)
    {
        if (!$this->hasCfImageResize() || !$cfParams) {
            return $url;
        }

        $cfPrefix    = $this->cfImgResizeZone;
        $baseFullUrl = $context->getScheme().'://'.$context->getHost().'/';

        // if $url is a url, then ours needs to be a full url too
        if (!preg_match('/^https?:\/\//i', $cfPrefix) && preg_match('/^https?:\/\//i', $url)) {
            $cfPrefix = $baseFullUrl.ltrim($cfPrefix, '/');
        }

        // source-url in cf needs to be a full url if the zone is a full url
        if (preg_match('/^https?:\/\//i', $cfPrefix) && !preg_match('/^https?:\/\//i', $url)) {
            $url = $baseFullUrl.ltrim($url, '/');
        }

        return $cfPrefix.'/cdn-cgi/image/'
            .implode(',', array_map(function ($k, $v) {
                return "$k=".urlencode($v);
            }, array_keys($cfParams), $cfParams))
            .'/'
            .$url;
    }
}
