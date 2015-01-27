<?php

namespace spec\Application\PortalBundle\Mode;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PortalModeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Application\PortalBundle\Mode\PortalModeFactory');
    }

    function it_remains_normal_if_does_not_match_a_mode()
    {
        $mode = $this->createMode($path = '/admin-mode-invalid/en/tickets');

        $mode->isNormal()->shouldReturn(true);
        $mode->getOriginalPath()->shouldReturn($path);
        $mode->getInternalPath()->shouldReturn($path);
    }

    function it_creates_admin_mode()
    {
        $mode = $this->createMode($path = '/admin-mode/en/tickets');

        $mode->isAdmin()->shouldReturn(true);
        $mode->getData()->shouldReturn(null);
        $mode->getOriginalPath()->shouldReturn($path);
        $mode->getInternalPath()->shouldReturn('/en/tickets');
        $mode->getModePath()->shouldReturn('/admin-mode');
    }

    function it_creates_brand_mode()
    {
        $mode = $this->createMode($path = '/brand-4/en/ticket/67');

        $mode->isBrand()->shouldReturn(true);
        $mode->getData()->shouldReturn(4);
        $mode->getOriginalPath()->shouldReturn($path);
        $mode->getInternalPath()->shouldReturn('/en/ticket/67');
        $mode->getModePath()->shouldReturn('/brand-4');
    }

    function it_creates_embed_mode()
    {
        $mode = $this->createMode($path = '/embed-142/en/ticket/67');

        $mode->isEmbed()->shouldReturn(true);
        $mode->getData()->shouldReturn(142);
        $mode->getOriginalPath()->shouldReturn($path);
        $mode->getInternalPath()->shouldReturn('/en/ticket/67');
        $mode->getModePath()->shouldReturn('/embed-142');
    }
}
