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

/**
 * DeskPRO.
 */
namespace DpTest\DeskPRO\Application\Brand;

use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DpTest\DeskProTestCase;

class BrandStackTest extends DeskProTestCase
{
    public function testTheStack()
    {
        $mockFactory = \Mockery::mock('DeskPRO\Bundle\PortalBundle\Brand\BrandContainerFactory');

        $mockBrand1    = \Mockery::mock('Application\DeskPRO\Entity\Brand');
        $mockThemeSet1 = \Mockery::mock('DeskPRO\AppBundle\Entity\ThemeSet');
        $mockBrand1->shouldReceive('getId')->andReturn(1);
        $mockBrand1->shouldReceive('getThemeSet')->andReturn($mockThemeSet1);
        $mockContainer1 = \Mockery::mock('DeskPRO\Bundle\PortalBundle\Brand\BrandContainer');

        $mockBrand2    = \Mockery::mock('Application\DeskPRO\Entity\Brand');
        $mockThemeSet2 = \Mockery::mock('DeskPRO\AppBundle\Entity\ThemeSet');
        $mockBrand2->shouldReceive('getId')->andReturn(2);
        $mockBrand1->shouldReceive('getThemeSet')->andReturn($mockThemeSet2);
        $mockContainer2 = \Mockery::mock('DeskPRO\Bundle\PortalBundle\Brand\BrandContainer');

        $mockFactory->shouldReceive('create')->with($mockBrand1)->andReturn($mockContainer1);
        $mockFactory->shouldReceive('create')->with($mockBrand2)->andReturn($mockContainer2);

        /*
         * As demonstrated below, the BrandStack lets you seamlessly move between different brand "containers" (eg. contexts)
         * through runtime. You can push(Brand entity) and pop() in an out of these container contexts.
         */
        $stack = new BrandStack($mockFactory, $mockBrand1);
        // There's always a brand, at least the default brand, even in CLI mode
        $this->assertSame($mockContainer1, $stack->getActive());

        $this->assertSame($mockContainer2, $stack->push($mockBrand2));

        $stack->pop();
        $this->assertSame($mockContainer1, $stack->getActive());

        $this->assertSame($mockContainer2, $stack->push($mockBrand2));

        $this->assertSame($mockContainer2, $stack->pop());
        $this->assertSame($mockContainer1, $stack->getActive());

        // testing that popping the last returns to default
        $this->assertSame($mockContainer1, $stack->pop());
        $this->assertSame($mockContainer1, $stack->getActive());
    }
}
