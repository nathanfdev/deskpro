<?php

namespace DeskPRO\Bundle\PortalBundle\Designer;

use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class AdminPreviewIframeListener.
 *
 * Allow loading in iframe if in preview mode
 */
class AdminPreviewIframeListener
{
    /**
     * @var PortalModeStorage
     */
    private $portal_mode_storage;

    /**
     * AdminPreviewIframeListener constructor.
     *
     * @param PortalModeStorage $portal_mode_storage
     */
    public function __construct(PortalModeStorage $portal_mode_storage)
    {
        $this->portal_mode_storage = $portal_mode_storage;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $mode = $this->portal_mode_storage->getMode();
        if ($mode && $mode->isAdminPreview()) {
            $event->getResponse()->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }
    }
}
