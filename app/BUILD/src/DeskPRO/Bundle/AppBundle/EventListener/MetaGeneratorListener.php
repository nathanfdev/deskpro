<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use Orb\Util\Strings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds the DeskPRO version as a meta tag to HTML pages.
 */
class MetaGeneratorListener implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', -10],
        ];
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        // Ignore if not master, if its empty, or if its not html
        if (!(
            $event->isMasterRequest()
            && !$event->getResponse()->isEmpty()
            && !$event->getRequest()->isXmlHttpRequest()
            && !RequestUtils::isLowRequest($event->getRequest())
            && strpos($event->getResponse()->headers->get('Content-Type', ''), 'text/html') !== false
            && !$event->getResponse() instanceof StreamedResponse
        )
        ) {
            return;
        }

        $version_id   = defined('DP_BUILD_TIME') && DP_BUILD_TIME ? DP_BUILD_TIME : time();
        $version_name = defined('DP_BUILD_NUM') && DP_BUILD_NUM ? DP_BUILD_NUM : 'DEV';
        $version_str  = "$version_name/$version_id";

        if (defined('DPC_IS_CLOUD')) {
            $software = 'DeskPRO.Cloud';
        } else {
            $software = 'DeskPRO';
        }

        $response = $event->getResponse();

        $new_html = Strings::strReplaceOne('</head>', "\n  <meta name=\"generator\" content=\"$software $version_str\" />\n</head>", $response->getContent());
        $response->setContent($new_html);
    }
}
