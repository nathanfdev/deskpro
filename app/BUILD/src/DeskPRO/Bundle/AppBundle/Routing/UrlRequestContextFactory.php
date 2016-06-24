<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

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
            return $this->createContextForSettingsUrl($defaultContext);
        }

        return $defaultContext;
    }

    /**
     * @param RequestContext $defaultContext
     *
     * @return RequestContext
     */
    private function createContextForSettingsUrl(RequestContext $defaultContext)
    {
        $brand = $this->container->get('brand_stack')->getActive()->getBrand();

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
