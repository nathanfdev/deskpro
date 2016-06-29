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

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Request\InterfaceInfo;
use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use DeskPRO\Bundle\AppBundle\Request\UrlCorrector;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Component\Util\DebugUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Detects if the URL is wrong and corrects it.
 */
class UrlCorrectorEventListener implements EventSubscriberInterface
{
    const MODE_REDIRECT = 'redirect';
    const MODE_ATTR     = 'attr';

    /**
     * @var InterfaceInfo
     */
    private $interfaceInfo;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var PortalModeStorage
     */
    private $portalModeStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param InterfaceInfo     $interfaceInfo
     * @param BrandStack        $brandStack
     * @param PortalModeStorage $portalModeStorage
     * @param LoggerInterface   $logger
     */
    public function __construct(InterfaceInfo $interfaceInfo, BrandStack $brandStack, PortalModeStorage $portalModeStorage, LoggerInterface $logger)
    {
        $this->interfaceInfo     = $interfaceInfo;
        $this->brandStack        = $brandStack;
        $this->portalModeStorage = $portalModeStorage;
        $this->logger            = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 125],
        ];
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onController(FilterControllerEvent $event)
    {
        if ($this->isExcludedEvent($event)) {
            return;
        }

        $request = $event->getRequest();
        $brand   = $this->brandStack->getActive();

        $options = [
            'autoCorrectScheme' => $brand->getSetting('core.deskpro_url_autocorrect'),
            'autoCorrectHost'   => $brand->getSetting('core.deskpro_url_autocorrect'),
            'helpdeskUrl'       => $brand->getSetting('core.deskpro_url'),
        ];

        $urlCorrector = new UrlCorrector($options);

        $corrections = $urlCorrector->getCorrections($request);

        if ($corrections) {
            if (DebugUtils::isLoggerHandling($this->logger, 'DBEUG')) {
                $this->logger->debug('[UrlCorrector] corrections: '.DebugUtils::varToString($options));
            }

            $url = $urlCorrector->getCorrectedUrl($request);
            $this->logger->debug('[UrlCorrector] Got URI: '.$request->getUri());
            $this->logger->debug('[UrlCorrector] Correct URI: '.$url);

            $mode = $this->getCorrectionMode($event);

            switch ($mode) {
                case self::MODE_REDIRECT:
                    $this->logger->debug('[UrlCorrector] Redirecting...');
                    $redirectResponse = new RedirectResponse($url, 301);
                    $redirectResponse->headers->setCookie(new Cookie('dp_autocorrect_url', 1));
                    $event->setController(function () use ($redirectResponse) {
                        return $redirectResponse;
                    });
                    break;

                case self::MODE_ATTR:
                    $this->logger->debug('[UrlCorrector] Setting request attributes');
                    $request->attributes->set('deskpro.url_corrector.corrections', $corrections);
                    $request->attributes->set('deskpro.url_corrector.correct_url', $url);
                    break;
            }
        } else {
            $this->logger->debug('[UrlCorrector] No corrections required for: '.$request->getUri());
        }
    }

    /**
     * Checks if the current event should be checked for url corrections.
     *
     * @param FilterControllerEvent $event
     *
     * @return bool True to skip the event
     */
    private function isExcludedEvent(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            $this->logger->debug('[UrlCorrector] Skip: Not master request');

            return true;
        }

        if (!$this->interfaceInfo->isInterfaceId([InterfaceInfo::ID_USER, InterfaceInfo::ID_ADMIN, InterfaceInfo::ID_AGENT])) {
            $this->logger->debug('[UrlCorrector] Skip: Not a specified interface');

            return true;
        }

        $request = $event->getRequest();

        if ($request->getMethod() !== 'GET') {
            $this->logger->debug('[UrlCorrector] Skip: Not a GET request');

            return true;
        }

        if (preg_match('#^/api/#i', $request->getPathInfo()) || preg_match('#^/portal/api/#i', $request->getPathInfo())) {
            $this->logger->debug('[UrlCorrector] Skip: Ignore API requests');

            return true;
        }

        if (RequestUtils::isLowRequest($request)) {
            $this->logger->debug('[UrlCorrector] Skip: Low-level request');

            return true;
        }

        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;
        if ($DP_ENV->getConfig('settings.disable_url_corrections')) {
            $this->logger->info('[UrlCorrector] Skip: settings.disable_url_corrections');

            return true;
        }

        $brand = $this->brandStack->getActive();
        if (!$brand) {
            $this->logger->info('[UrlCorrector] Skip: No active brand');

            return true;
        }

        if ($request->getHost() === 'deskpro-dev' || $brand->getSetting('core.deskpro_url') === 'http://deskpro-dev/') {
            $this->logger->info('[UrlCorrector] Skip: Using the special deskpro-dev host');

            return true;
        }

        if (!$brand->getSetting('core.setup_initial')) {
            $this->logger->info('[UrlCorrector] Skip: Not set up yet');

            return true;
        }

        $portalMode = $this->portalModeStorage->getMode();
        if ($portalMode && $portalMode->isFocusWindow()) {
            $this->logger->info('[UrlCorrector] Skip: Portal is in focus-window mode');

            return true;
        }

        return false;
    }

    /**
     * @param FilterControllerEvent $event
     *
     * @return string
     */
    private function getCorrectionMode(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if ($this->interfaceInfo->isInterfaceId([InterfaceInfo::ID_AGENT, InterfaceInfo::ID_ADMIN])) {
            if (strpos($request->getPathInfo(), '/login') !== false) {
                // admin and agent logins are handled in the controller so we can show info
                return self::MODE_ATTR;
            }
        }

        return self::MODE_REDIRECT;
    }
}
