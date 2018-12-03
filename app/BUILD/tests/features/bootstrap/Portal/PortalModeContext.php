<?php

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
        }
    }
}
