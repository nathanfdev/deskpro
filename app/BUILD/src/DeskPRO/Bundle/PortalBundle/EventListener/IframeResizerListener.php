<?php

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
