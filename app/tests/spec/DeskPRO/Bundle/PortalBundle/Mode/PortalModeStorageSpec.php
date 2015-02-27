<?php

namespace spec\DeskPRO\Bundle\PortalBundle\Mode;

use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PortalModeStorageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage');
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

    function it_will_give_you_the_serialized_mode()
    {
        $this->setMode(new PortalMode('/'));
        $this->getSerializedMode()->shouldBeString();
    }
}
