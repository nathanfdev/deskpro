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

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Helper\IsProxyRequestHelper;
use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PortalModeListener implements EventSubscriberInterface
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

    public function __construct(PortalModeFactory $factory, PortalModeStorage $store, LoggerInterface $logger)
    {
        $this->factory = $factory;
        $this->store   = $store;
        $this->logger  = $logger;
    }

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

    protected function logMode(PortalMode $mode)
    {
        $this->logger->info(sprintf('setting portal mode to "%s"', $mode));
    }

    public static function getSubscribedEvents()
    {
        // find the mode before LanguageStackInitializerListener
        return array(KernelEvents::REQUEST => array('onKernelRequest', 513));
    }
}
