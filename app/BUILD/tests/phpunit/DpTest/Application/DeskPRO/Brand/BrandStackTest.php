<?php

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
