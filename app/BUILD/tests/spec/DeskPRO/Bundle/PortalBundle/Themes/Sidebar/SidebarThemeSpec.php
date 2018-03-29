<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Themes\Sidebar;

use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Themes\Sidebar\SidebarTheme
 */
class SidebarThemeSpec extends ObjectBehavior
{
    public function it_is_a_theme()
    {
        $this->shouldHaveType('DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface');
    }

    public function it_has_the_correct_id_and_name()
    {
        $this->getId()->shouldReturn('sidebar');
        $this->getName()->shouldReturn('Sidebar');
    }

    public function it_is_a_child_of_the_base_theme()
    {
        $this->getParentId()->shouldReturn('base');
    }
}
