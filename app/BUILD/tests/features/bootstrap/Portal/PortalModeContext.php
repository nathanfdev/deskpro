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

/**
 * DeskPRO.
 */

namespace DpBehat\Portal;

use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DpBehat\RebootableContextInterface;

class PortalModeContext extends BasePortalContext implements RebootableContextInterface
{
    /**
     * @var PortalModeStorage
     */
    private $mode_storage;
    /**
     * @var PortalModeFactory
     */
    private $mode_factory;

    public function rebootContext()
    {
        $this->resetPortalModeContext();
    }

    public function resetPortalModeContext()
    {
        $this->mode_storage = $this->get('portal_mode_storage');
        $this->mode_factory = $this->get('portal_mode_factory');
    }

    /**
     * @Given the active mode is :set_mode
     */
    public function theActiveModeIsNormal($set_mode)
    {
        switch ($set_mode) {
            case 'admin':
                $mode = $this->mode_factory->createMode('/admin-mode');
                break;
            case 'brand':
                $mode = $this->mode_factory->createMode('/brand-1');
                break;
            default:
                $mode = $this->mode_factory->createMode('/');
                break;
        }

        $this->mode_storage->setMode($mode);
    }

    /**
     * @Then the portal should be in :mode mode
     */
    public function thePortalShouldBeInMode($mode)
    {
        $mode = $this->mode_storage->getMode();
        switch ($mode) {
            case 'admin':
                expect($mode->isAdmin())->toBe(true);
                break;
            case 'normal':
                expect($mode->isNormal())->toBe(true);
                break;
            case 'brand':
                expect($mode->isBrand())->toBe(true);
                break;
        }
    }
}
