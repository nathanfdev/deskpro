<?php

namespace DeskPRO\Bundle\AppBundle\Request;

use DeskPRO\Bundle\AppBundle\Routing\RouterWithDynamicContext;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class OriginalUrlGenerator.
 */
class OriginalUrlGenerator
{
    /**
     * @var UrlGeneratorInterface
     */
    private $baseUrlGenerator;

    /**
     * @var BrandAwareSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $baseUrlGenerator
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param RequestStack $requestStack
     */
    public function __construct(UrlGeneratorInterface $baseUrlGenerator, BrandAwareSettingsResolver $settingsResolver, RequestStack $requestStack)
    {
        $this->baseUrlGenerator = $baseUrlGenerator;
        $this->settingsResolver = $settingsResolver;
        $this->requestStack     = $requestStack;
    }

    /**
     * @param Request $request
     * @param string  $name
     * @param array   $parameters
     * @param int     $referenceType
     *
     * @return string
     */
    public function generate(Request $request, $name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $router = $this->baseUrlGenerator;
        if ($router instanceof RouterWithDynamicContext) {
            $router = $router->getBaseRouter();
        }

        $originalContext = $router->getContext();
        $request         = $this->requestStack->getMasterRequest() ?: $request;

        if ($request->getHost()) {
            $originalContext->fromRequest($request);
        } else {
            $originalContext = $this->getUrlHostInfo($originalContext);
        }
        
        $router->setContext($originalContext);

        return $router->generate($name, $parameters, $referenceType);
    }

    public function getUrlHostInfo($context)
    {
        $deskproUrl = $this->settingsResolver->getSetting('core.deskpro_url');
        if ($deskproUrl && $info = parse_url(rtrim($deskproUrl, '/'))) {
            $context->setScheme($info['scheme']);
            $context->setBaseUrl(!empty($info['path']) ? $info['path'] : $context->getBaseUrl());
            $context->setHost(!empty($info['host']) ? $info['host'] : 'localhost');
        }

        return $context;
    }
}
