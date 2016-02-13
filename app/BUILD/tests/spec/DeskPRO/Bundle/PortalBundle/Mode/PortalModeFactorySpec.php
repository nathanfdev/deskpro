<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

    public function it_creates_brand_mode()
    {
        $mode = $this->createMode($path = '/brand-4/en/ticket/67');

        $mode->isBrand()->shouldReturn(true);
        $mode->getData()->shouldReturn(4);
        $mode->getOriginalPath()->shouldReturn($path);
        $mode->getInternalPath()->shouldReturn('/en/ticket/67');
        $mode->getModePath()->shouldReturn('/brand-4');
    }

    public function it_creates_brand_mode_on_homepage()
    {
        $mode = $this->createMode($path = '/brand-4');

        $mode->isBrand()->shouldReturn(true);
        $mode->getData()->shouldReturn(4);
        $mode->getOriginalPath()->shouldReturn($path);
        $mode->getInternalPath()->shouldReturn('/');
        $mode->getModePath()->shouldReturn('/brand-4');
    }
}
