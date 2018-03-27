<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Orb\Util\Strings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Dynamically adds Segment tracking code to html pages.
 */
class SegmentTrackingListener implements EventSubscriberInterface
{
    /**
     * @var Person|null
     */
    private $person = null;

    /**
     * @var array
     */
    private $settings;

    /**
     * @param TokenStorage     $tokenStorage
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(TokenStorage $tokenStorage, SettingsResolver $settingsResolver)
    {
        $token = $tokenStorage->getToken();
        if ($token && $token->getUser() instanceof Person) {
            $this->person = $token->getUser();
        }
        $this->settings = $settingsResolver->getGlobalSettings()->get('segment');
    }

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
        $settings = $this->settings;
        $person   = $this->person;

        if (!$this->settings || !$this->settings['enabled']) {
            return;
        }
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

        if ($person && $person->is_agent && DP_INTERFACE !== 'user') {
            $segment_key = $settings['agent_project_key'];
        } elseif (DP_INTERFACE === 'user') {
            $segment_key = $settings['portal_project_key'];
        } else {
            $segment_key = null;
        }

        if (!$segment_key) {
            return;
        }

        $track_ident = '';

        if ($person) {
            $data = json_encode([
                'name'     => $person->getDisplayName(false),
                'email'    => $person->getEmailAddress(),
                'isAgent'  => $person->is_agent,
                'canAdmin' => $person->can_admin,
            ]);
            $track_ident .= "analytics.identify('{$person->getAccountTrackingId()}', $data);";
        }

        if ($group_data = $settings['group_data']) {
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
