<?php

namespace Application\PortalBundle\Mode;

/**
 * Super simple service that just maintains state of a mode for the current request.
 *
 * This is used by the PortalModeListener, which sets the mode. This is consumed by
 * the router and other services who want to be "mode aware". see service: portal_mode_storage
 */
class PortalModeStorage
{
    /**
     * @var PortalMode
     */
    protected $mode;

    /**
     * @return PortalMode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param PortalMode $mode
     */
    public function setMode(PortalMode $mode)
    {
        $this->mode = $mode;
    }
}
