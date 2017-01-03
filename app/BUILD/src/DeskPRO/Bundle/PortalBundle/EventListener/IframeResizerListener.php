<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Orb\Util\Strings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Installs helper script on embed frames that enable
 * iframe resizer listener.
 */
class IframeResizerListener implements EventSubscriberInterface
{
    /**
     * @var PortalModeStorage
     */
    private $portalModeStorage;

    /**
     * IframeResizerListener constructor.
     *
     * @param PortalModeStorage $portalModeStorage
     */
    public function __construct(PortalModeStorage $portalModeStorage)
    {
        $this->portalModeStorage = $portalModeStorage;
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $portalMode = $this->portalModeStorage->getMode();
        if (!(
            $portalMode
            && $event->isMasterRequest()
            && !$event->getResponse()->isEmpty()
            && !$event->getRequest()->isXmlHttpRequest()
            && ($portalMode->isFocusWindow() || $portalMode->isFrameEmbed())
        )) {
            return;
        }

        $script = '<script>'.file_get_contents(__DIR__.'/iframeResizer.contentWindow.min.js').'</script>';

        $response = $event->getResponse();

        $new_html = Strings::strReplaceOne('</head>', "\n  $script\n</head>", $response->getContent());
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
