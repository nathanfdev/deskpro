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

use Application\DeskPRO\App;
use Orb\Util\Strings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Dynamically adds Segment tracking code to html pages.
 */
class SegmentTrackingListener implements EventSubscriberInterface
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
        global $DP_CONFIG;

        if (!defined('DP_INTERFACE')) {
            return;
        }

        if (!class_exists('Application\DeskPRO\App', false)) {
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

        try {
            $person = App::getCurrentPerson();
        } catch (\Exception $e) {
            $person = null;
        }

        if ($person && $person->is_agent && DP_INTERFACE !== 'user') {
            $segment_key = @$DP_CONFIG['segment_config']['agent_project_key'];
        } elseif (DP_INTERFACE === 'user') {
            $segment_key = @$DP_CONFIG['segment_config']['portal_project_key'];
        } else {
            $segment_key = null;
        }

        if (!$segment_key) {
            return;
        }

        $track_ident = '';

        if ($person) {
            if ($person->is_agent) {
                $user_type = $person->can_admin ? 'admin' : 'agent';
            } else {
                $user_type = 'user';
            }

            $data = json_encode(array(
                'name'     => $person->getDisplayName(false),
                'email'    => $person->getEmailAddress(),
                'userType' => $user_type,
            ));
            $track_ident .= "analytics.identify('{$person->getAccountTrackingId()}', $data);";
        }

        if ($group_data = @$DP_CONFIG['segment_config']['group_data']) {
            $data = json_encode($group_data['data']);
            $track_ident .= "analytics.group('{$group_data['group_id']}', $data);";
        }

        $tack_html = <<<HTML
<script type="text/javascript">
!function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","page","once","off","on"];analytics.factory=function(t){return function(){var e=Array.prototype.slice.call(arguments);e.unshift(t);analytics.push(e);return analytics}};for(var t=0;t<analytics.methods.length;t++){var e=analytics.methods[t];analytics[e]=analytics.factory(e)}analytics.load=function(t){var e=document.createElement("script");e.type="text/javascript";e.async=!0;e.src=("https:"===document.location.protocol?"https://":"http://")+"cdn.segment.com/analytics.js/v1/"+t+"/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(e,n)};analytics.SNIPPET_VERSION="3.1.0";
analytics.load("{$segment_key}");
{$track_ident}
analytics.page()
}}();
</script>
HTML;

        $response = $event->getResponse();
        $new_html = $response->getContent();

        $new_html = Strings::strReplaceOne('</head>', "\n  $tack_html\n</head>", $new_html);

        $response->setContent($new_html);
    }
}
