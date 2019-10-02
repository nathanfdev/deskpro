<?php

namespace DeskPRO\Bundle\AppBundle\Request;

use DeskPRO\Bundle\AppBundle\Routing\RouterWithDynamicContext;
use Symfony\Component\HttpFoundation\Request;
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
     * Constructor.
     *
     * @param UrlGeneratorInterface $baseUrlGenerator
     */
    public function __construct(UrlGeneratorInterface $baseUrlGenerator)
    {
        $this->baseUrlGenerator = $baseUrlGenerator;
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

        try {
            $originalContext = $router->getContext();
            $globalContext   = clone $originalContext;
            $globalContext->setBaseUrl($request->getBaseUrl());

            $router->setContext($globalContext);

            return $router->generate($name, $parameters, $referenceType);
        } finally {
            $router->setContext($originalContext);
        }

        return $router->generate($name, $parameters, $referenceType);
    }
}
