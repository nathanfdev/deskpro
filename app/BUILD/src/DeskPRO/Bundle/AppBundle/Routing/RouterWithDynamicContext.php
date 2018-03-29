<?php

namespace DeskPRO\Bundle\AppBundle\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * This decorates a router to add the ability for the context to be dynamic.
 *
 * Every time generate() is used, a new context can optionally be created.
 * The purpose is so we can change the URL based on settings. For example,
 * the base URL may change when we need to send an email regarding a
 * specific brand (e.g. the context factory might use the brand stack
 * to create a new context).
 *
 * We need this for brands, but this is also used to set the proper
 * URL based on settings when we need to generate an absolute URL.
 * For example, if we send an email on the CLI, then there is no default
 * context populated from a Request, we need to create one ourselves
 * based on settings.
 */
class RouterWithDynamicContext implements RouterInterface, RouterDecorator, RequestMatcherInterface, WarmableInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var UrlRequestContextFactory
     */
    private $contextFactory;

    /**
     * RouterWithDynamicContext constructor.
     *
     * @param RouterInterface          $router
     * @param UrlRequestContextFactory $contextFactory
     */
    public function __construct(RouterInterface $router, UrlRequestContextFactory $contextFactory = null)
    {
        $this->router         = $router;
        $this->contextFactory = $contextFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRouter()
    {
        return $this->router;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        $prevContext = $this->getContext();

        if ($this->contextFactory) {
            $context = $this->contextFactory->createGenerateContext(
                $prevContext,
                $name,
                $parameters,
                $referenceType
            );
        } else {
            $context = $prevContext;
        }

        if (isset($parameters['brand'])) {
            unset($parameters['brand']);
        }

        if ($context !== $prevContext) {
            $this->setContext($context);
        }

        try {
            return $this->getBaseRouter()->generate($name, $parameters, $referenceType);
        } finally {
            if ($context !== $prevContext) {
                $this->setContext($prevContext);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathInfo)
    {
        return $this->router->match($pathInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function matchRequest(Request $request)
    {
        if ($this->router instanceof RequestMatcherInterface) {
            return $this->router->matchRequest($request);
        }

        return $this->router->match($request->getPathInfo());
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        if ($this->router instanceof WarmableInterface) {
            $this->router->warmUp($cacheDir);
        }
    }
}
