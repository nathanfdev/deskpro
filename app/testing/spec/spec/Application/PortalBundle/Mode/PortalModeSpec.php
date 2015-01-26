<?php

namespace spec\Application\PortalBundle\Mode;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PortalModeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Application\PortalBundle\Mode\PortalMode');
    }

    public function let()
    {
        $this->beConstructedWith('/initial/path/is/set');
    }

    function it_has_normal_mode_by_default()
    {
        $this->isAdmin()->shouldReturn(false);
        $this->isNormal()->shouldReturn(true);
    }

    function it_can_be_in_admin_mode()
    {
        $this->setAdmin();

        $this->isAdmin()->shouldReturn(true);
        $this->isNormal()->shouldReturn(false);
    }

    function it_can_be_in_brand_mode()
    {
        $this->setBrand($data = 4);

        $this->isBrand()->shouldReturn(true);
        $this->getData()->shouldReturn($data);
        $this->isAdmin()->shouldReturn(false);
        $this->isNormal()->shouldReturn(false);
    }

    function it_contains_some_path_info()
    {
        $this->setOriginalPath($orig = '/some/path');
        $this->getOriginalPath()->shouldReturn($orig);
        $this->setInternalPath($internal = '/path');
        $this->getInternalPath()->shouldReturn($internal);
    }

    function it_requires_a_path_in_the_constrcutor_and_initiates_original_and_internal_paths()
    {
        $this->beConstructedWith($path = '/some/path');
        $this->getOriginalPath()->shouldReturn($path);
        $this->getInternalPath()->shouldReturn($path);
    }
}
