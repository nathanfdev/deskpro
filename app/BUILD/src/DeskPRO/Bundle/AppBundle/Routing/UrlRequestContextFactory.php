<?php

namespace DeskPRO\Bundle\AppBundle\Routing;

use Application\DeskPRO\Entity\Brand;
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
            return $this->createContextForSettingsUrl($defaultContext, $parameters);
        }

        return $defaultContext;
    }

    /**
     * @param RequestContext $defaultContext
     * @param array          $parameters
     *
     * @return RequestContext
     */
    private function createContextForSettingsUrl(RequestContext $defaultContext, array $parameters)
    {
        /** @var Brand $brand */
        $brand = array_key_exists('brand', $parameters) && $parameters['brand'] instanceof Brand
            ? $parameters['brand']
            : $this->container->get('brand_stack')->getActive()->getBrand();

        if (isset($this->absContextToBrand[$brand->getId()])) {
            return $this->absContextToBrand[$brand->getId()];
        }

        $url = $this->container->get('settings_resolver')->getBrandSettings($brand)->get('core.deskpro_url')
            ?: $this->container->get('settings_resolver')->getGlobalSettings()->get('core.deskpro_url');

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

        $this->absContextToBrand[$brand->getId()] = $context;

        return $context;
    }
}
