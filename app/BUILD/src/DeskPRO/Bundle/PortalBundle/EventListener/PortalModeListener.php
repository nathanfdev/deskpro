<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Helper\IsProxyRequestHelper;
use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This request listener sets up the portal mode.
 */
class PortalModeListener implements EventSubscriberInterface, SkipLowRequestInterface
{
    /**
     * @var PortalModeFactory
     */
    private $factory;

    /**
     * @var PortalModeStorage
     */
    private $store;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param PortalModeFactory $factory
     * @param PortalModeStorage $store
     * @param LoggerInterface   $logger
     */
    public function __construct(PortalModeFactory $factory, PortalModeStorage $store, LoggerInterface $logger)
    {
        $this->factory = $factory;
        $this->store   = $store;
        $this->logger  = $logger;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest() || IsProxyRequestHelper::check($event->getRequest())) {
            // dont set a portal mode for subrequests
            // also, don't set a portal mode for master requests that are a proxy (ESI)
            return;
        }

        $this->logger->info('attempting to detect portal mode');

        $request = $event->getRequest();

        $path = $request->getPathInfo();
        if ('/_' === substr($path, 0, 2)) {
            // ignore any other path that starts with _ (profiler and such)
            return;
        }

        $mode = $this->factory->createMode($request->getPathInfo());

        $this->store->setMode($mode);

        $this->logMode($mode);
    }

    /**
     * @param PortalMode $mode
     */
    protected function logMode(PortalMode $mode)
    {
        $this->logger->info(sprintf('setting portal mode to "%s"', $mode));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        // find the mode before LanguageStackInitializerListener
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 513],
        ];
    }
}
