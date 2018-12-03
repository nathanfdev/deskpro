<?php

namespace spec\DeskPRO\Bundle\PortalBundle\Mode;

use PhpSpec\ObjectBehavior;

class PortalModeFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory');
    }

    public function it_remains_normal_if_does_not_match_a_mode()
    {
        $mode = $this->createMode($path = '/admin-mode-invalid/en/tickets');

        $mode->isNormal()->shouldReturn(true);
        $mode->getOriginalPath()->shouldReturn($path);
        $mode->getInternalPath()->shouldReturn($path);
    }

    public function it_creates_admin_mode()
    {
        $mode = $this->createMode($path = '/admin-mode/en/tickets');

        $mode->isAdmin()->shouldReturn(true);
        $mode->getData()->shouldReturn(null);
        $mode->getOriginalPath()->shouldReturn($path);
        $mode->getInternalPath()->shouldReturn('/en/tickets');
        $mode->getModePath()->shouldReturn('/admin-mode');
    }

    public function it_creates_admin_mode_on_homepage()
    {
        $mode = $this->createMode($path = '/admin-mode');

        $mode->isAdmin()->shouldReturn(true);
        $mode->getData()->shouldReturn(null);
        $mode->getOriginalPath()->shouldReturn($path);
        $mode->getInternalPath()->shouldReturn('/');
        $mode->getModePath()->shouldReturn('/admin-mode');
    }
}
