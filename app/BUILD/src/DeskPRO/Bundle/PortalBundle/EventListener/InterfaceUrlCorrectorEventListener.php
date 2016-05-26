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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Request\InterfaceInfo;
use DeskPRO\Component\Util\RegexUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Detects requests to LANG/agent|admin|reports and removes the lang portion.
 */
class InterfaceUrlCorrectorEventListener implements EventSubscriberInterface
{
    /**
     * @var InterfaceInfo
     */
    private $interfaceInfo;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(InterfaceInfo $interfaceInfo, LoggerInterface $logger)
    {
        $this->interfaceInfo = $interfaceInfo;
        $this->logger        = $logger;
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request  = $event->getRequest();
        $pathInfo = '/'.ltrim($request->getPathInfo(), '/');

        $regex    = '#^/(?P<locale>(?:[a-z]{2}_[A-Z0-9]{2}|[a-z]{2}))/(?P<iface>agent|admin|reports)(?:/(?P<path>.*?))?$#';
        $urlParts = RegexUtils::getMatches($regex, $pathInfo);

        // Not a URL format we care about
        if (!$urlParts || empty($urlParts['iface'])) {
            return;
        }

        if (empty($urlParts['path'])) {
            $urlParts['path'] = '';
        }

        $newUrl = $request->getUriForPath("/{$urlParts['iface']}/{$urlParts['path']}");
        $this->logger->warning(sprintf('[InterfaceUrlCorrector] Correcting: %s -> %s', $pathInfo, $newUrl));

        $event->setResponse(new RedirectResponse($newUrl));
        $event->stopPropagation();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', -1],
        ];
    }
}
