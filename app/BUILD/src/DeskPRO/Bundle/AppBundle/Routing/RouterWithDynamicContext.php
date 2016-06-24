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
class RouterWithDynamicContext implements RouterInterface, RouterDecorator
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
    public function match($pathinfo)
    {
        return $this->router->match($pathinfo);
    }
}
