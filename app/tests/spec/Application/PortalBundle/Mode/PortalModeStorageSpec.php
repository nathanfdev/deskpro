<?php

namespace spec\Application\PortalBundle\Mode;

use Application\PortalBundle\Mode\PortalMode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PortalModeStorageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Application\PortalBundle\Mode\PortalModeStorage');
    }

    function it_starts_with_no_mode()
    {
        $this->getMode()->shouldReturn(null);
    }

    function it_can_save_and_retrieve_a_mode(PortalMode $mode)
    {
        $this->setMode($mode);
        $this->getMode()->shouldReturn($mode);
    }
}
