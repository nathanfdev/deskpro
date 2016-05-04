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
     * BugsnagJsListener constructor.
     *
     * @param AppEnv $appEnv
     */
    public function __construct(AppEnv $appEnv)
    {
        $this->settings = $appEnv->getConfig('settings.bugsnag', []);
    }

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
        $settings = $this->settings;

        if (!$settings || !@$settings['enable_js'] || !@$settings['api_key']) {
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

        $code = <<<CODE
<script src="//d2wy8f7a9ursnm.cloudfront.net/bugsnag-2.min.js" data-apikey="{$settings['api_key']}"></script>
CODE;
        $response = $event->getResponse();
        $html     = $response->getContent();
        $html     = Strings::strReplaceOne('</head>', "\n  $code\n</head>", $html);
        $response->setContent($html);
    }
}
