<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use Orb\Util\Strings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class BugsnagJsListener.
 */
class BugsnagJsListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $settings;

    /**
     * @var AppEnv
     */
    private $env;

    /**
     * BugsnagJsListener constructor.
     *
     * @param AppEnv $appEnv
     */
    public function __construct(AppEnv $appEnv)
    {
        $this->settings = $appEnv->getConfig('settings.bugsnag', []);
        $this->env      = $appEnv;
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

        if (!$settings || !@$settings['frontend_api_key']) {
            return;
        }

        // Ignore if not master, if its empty, or if its not html
        if (!(
            $event->isMasterRequest()
            && !$event->getResponse()->isEmpty()
            && !$event->getRequest()->isXmlHttpRequest()
            && strpos($event->getResponse()->headers->get('Content-Type', ''), 'text/html') !== false
        )
        ) {
            return;
        }

        $data = ['apikey' => $settings['frontend_api_key']];
        if (isset($settings['app_version']) && $settings['app_version']) {
            $data['appversion'] = $settings['app_version'];
        } else {
            $data['appversion'] = $this->env->getVersionName().'.'.$this->env->getBuildId() ?: $this->env->getBuildId() ?: 'DEV';
        }

        $strings = [];
        foreach ($data as $key => $value) {
            $strings[] = sprintf('data-%s="%s"', $key, $value);
        }
        $add = implode(' ', $strings);
        if (isset($settings['metadata']) && is_array($settings['metadata'])) {
            $metadata = ['deskpro' => $settings['metadata']];
            $metadata = json_encode($metadata, JSON_PRETTY_PRINT);
            $metadata = <<<CODE
            <script>
            if (window.Bugsnag) Bugsnag.metaData = {$metadata};
            </script>
CODE;
        }

        $code = <<<CODE
<script src="//d2wy8f7a9ursnm.cloudfront.net/bugsnag-2.min.js" {$add}></script>
{$metadata}
CODE;
        $response = $event->getResponse();
        $html     = $response->getContent();
        $html     = Strings::strReplaceOne('</head>', "\n  $code\n</head>", $html);
        $response->setContent($html);
    }
}
