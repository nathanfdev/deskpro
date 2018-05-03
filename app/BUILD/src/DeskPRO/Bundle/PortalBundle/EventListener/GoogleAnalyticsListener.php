<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use Orb\Util\Strings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class GoogleAnalyticsListener implements EventSubscriberInterface
{
    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    public function __construct(SettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    public function onResponse(FilterResponseEvent $event)
    {
        // Ignore if not master, if its empty, or if its not html
        if (!(
            $event->isMasterRequest()
            && !$event->getResponse()->isEmpty()
            && !$event->getRequest()->isXmlHttpRequest()
            && !RequestUtils::isLowRequest($event->getRequest())
        )) {
            return;
        }

        $gaTrackingId = $this->settingsResolver->getGlobalSettings()->get('core.ga_property_id', false);

        if (!$gaTrackingId) {
            return;
        }

        $displayFeatures = '';
        if ($this->settingsResolver->getGlobalSettings()->get('core.ga_property_demographics_reports', false)) {
            $displayFeatures = "ga('require', 'displayfeatures')";
        }

        $enhancedLink = '';
        if ($this->settingsResolver->getGlobalSettings()->get('core.ga_property_enhanced_link', false)) {
            $enhancedLink = "ga('require', 'linkid');";
        }

        $gaScript = "<script>
              (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
              (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
              m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
              })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
            
              ga('create', '$gaTrackingId', 'auto');
              $displayFeatures
              $enhancedLink
              ga('send', 'pageview');
            
            </script>";

        $response = $event->getResponse();

        $new_html = Strings::strReplaceOne('</body>', "\n  $gaScript\n</body>", $response->getContent());
        $response->setContent($new_html);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        // find the mode before LanguageStackInitializerListener
        return [
            KernelEvents::RESPONSE => ['onResponse', 35],
        ];
    }
}
