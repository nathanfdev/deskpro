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

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use Orb\Util\Strings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
