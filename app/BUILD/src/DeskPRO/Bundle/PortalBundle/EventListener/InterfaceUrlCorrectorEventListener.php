<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
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
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param InterfaceInfo   $interfaceInfo
     * @param LanguageManager $languageManager
     * @param LoggerInterface $logger
     */
    public function __construct(InterfaceInfo $interfaceInfo, LanguageManager $languageManager, LoggerInterface $logger)
    {
        $this->interfaceInfo   = $interfaceInfo;
        $this->languageManager = $languageManager;
        $this->logger          = $logger;
    }

    /**
     * @internal
     *
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->languageManager->isMultiLanguagePortal()) {
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
        $this->logger->debug(sprintf('[InterfaceUrlCorrector] Correcting: %s -> %s', $pathInfo, $newUrl));

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
