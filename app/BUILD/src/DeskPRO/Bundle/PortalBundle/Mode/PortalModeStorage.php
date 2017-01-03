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

namespace DeskPRO\Bundle\PortalBundle\Mode;

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

    public function getSerializedMode()
    {
        return serialize($this->mode);
    }

    /**
     * @param PortalMode $mode
     */
    public function setMode(PortalMode $mode)
    {
        $this->mode = $mode;
    }
}
