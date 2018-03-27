<?php

namespace spec\DeskPRO\Bundle\PortalBundle\Mode;

use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use PhpSpec\ObjectBehavior;

class PortalModeSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('DeskPRO\Bundle\PortalBundle\Mode\PortalMode');
    }

    public function let()
    {
        $this->beConstructedWith('/initial/path/is/set');
    }

    public function it_has_normal_mode_by_default()
    {
        $this->isAdmin()->shouldReturn(false);
        $this->isAdminPreview()->shouldReturn(false);
        $this->isBrand()->shouldReturn(false);
        $this->isNormal()->shouldReturn(true);
    }

    public function it_can_be_in_admin_mode()
    {
        $this->setAdmin();

        $this->isAdmin()->shouldReturn(true);
        $this->isNormal()->shouldReturn(false);
    }

    public function it_can_be_in_brand_mode()
    {
        $this->setBrand($data = 4);

        $this->isBrand()->shouldReturn(true);
        $this->getData()->shouldReturn($data);
        $this->isAdmin()->shouldReturn(false);
        $this->isNormal()->shouldReturn(false);
    }

    public function it_can_be_in_admin_preview_mode()
    {
        $this->setAdminPreview();

        $this->isAdminPreview()->shouldReturn(true);
        $this->isNormal()->shouldReturn(false);
    }

    public function it_contains_some_path_info()
    {
        $this->setOriginalPath($orig = '/some/path');
        $this->getOriginalPath()->shouldReturn($orig);
        $this->setInternalPath($internal = '/path');
        $this->getInternalPath()->shouldReturn($internal);
        $this->setModePath($mpath = '/mode');
        $this->getModePath()->shouldReturn($mpath);
    }

    public function it_requires_a_path_in_the_constrcutor_and_initiates_original_and_internal_paths()
    {
        $this->beConstructedWith($path = '/some/path');
        $this->getOriginalPath()->shouldReturn($path);
        $this->getInternalPath()->shouldReturn($path);
        $this->getModePath()->shouldReturn(null);
    }

    public function it_can_make_a_sensible_string_when_normal_mode()
    {
        $this->__toString()->shouldReturn(PortalMode::MODE_NORMAL);
    }

    public function it_can_make_a_sensible_string_when_admin_mode()
    {
        $this->setAdmin();
        $this->__toString()->shouldReturn(PortalMode::MODE_ADMIN);
    }

    public function it_can_make_a_sensible_string_when_brand_mode()
    {
        $this->setBrand(5);
        $this->__toString()->shouldReturn(sprintf('%s [ID=%s]', PortalMode::MODE_BRAND, 5));
    }
}
