<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Brand
 */

namespace DpUnitTests\DeskPRO\Brand;


use Application\DeskPRO\Brand\BrandStack;

class BrandStackTest extends \DpUnitTestCase
{
	public function testTheStack()
	{
		$mockFactory = \Mockery::mock('Application\DeskPRO\Brand\BrandContainerFactory');

		$mockBrand1    = \Mockery::mock('Application\DeskPRO\Entity\Brand');
		$mockBrand1->shouldReceive('getId')->andReturn(1);
		$mockContainer1 = \Mockery::mock('Application\DeskPRO\Brand\BrandContainer');

		$mockBrand2    = \Mockery::mock('Application\DeskPRO\Entity\Brand');
		$mockBrand2->shouldReceive('getId')->andReturn(2);
		$mockContainer2 = \Mockery::mock('Application\DeskPRO\Brand\BrandContainer');

		$mockFactory->shouldReceive('create')->with($mockBrand1)->andReturn($mockContainer1);
		$mockFactory->shouldReceive('create')->with($mockBrand2)->andReturn($mockContainer2);

		/**
		 * As demonstrated below, the BrandStack lets you seamlessly move between different brand "containers" (eg. contexts)
		 * through runtime. You can push(Brand entity) and pop() in an out of these container contexts.
		 */
		$stack = new BrandStack($mockFactory, $mockBrand1);
		$this->assertSame(null, $stack->getActive());

		$stack->push($mockBrand1);
		$this->assertSame($mockContainer1, $stack->getActive());

		$this->assertSame($mockContainer2, $stack->push($mockBrand2));

		$stack->pop();
		$this->assertSame($mockContainer1, $stack->getActive());

		$this->assertSame($mockContainer2, $stack->push($mockBrand2));

		$stack->pop();
		$this->assertSame($mockContainer1, $stack->getActive());

		$stack->pop();
		$this->assertSame(null, $stack->getActive());
	}
}
