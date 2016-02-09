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

/**
 * DeskPRO.
 */
namespace Application\DeskPRO\EventListener;

use Orb\Util\Strings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Dynamically adds tracking code to html pages
 * (typically used on cloud only).
 */
class HtmlTrackingListener implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onResponse', -10),
        );
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        global $DP_CUSTOM_HEAD_HTML, $DP_CUSTOM_END_HTML;

        if (!$DP_CUSTOM_HEAD_HTML && !$DP_CUSTOM_END_HTML) {
            return;
        }

        // Ignore if not master, if its empty, or if its not html
        if (!(
            $event->isMasterRequest()
            && !$event->getResponse()->isEmpty()
            && !$event->getRequest()->isXmlHttpRequest()
            && strpos($event->getResponse()->headers->get('Content-Type', ''), 'text/html') !== false
        )) {
            return;
        }

        $response = $event->getResponse();

        $new_html = $response->getContent();

        if ($DP_CUSTOM_HEAD_HTML) {
            $new_html = Strings::strReplaceOne('</head>', "\n  $DP_CUSTOM_HEAD_HTML\n</head>", $new_html);
        }
        if ($DP_CUSTOM_END_HTML) {
            $new_html = Strings::strReplaceOne('</body>', "$DP_CUSTOM_END_HTML\n</body>", $new_html);
        }

        $response->setContent($new_html);
    }
}
