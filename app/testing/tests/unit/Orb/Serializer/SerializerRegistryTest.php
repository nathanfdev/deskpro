<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage
 */

namespace DpUnitTests\Orb\Serializer;

use Orb\Serializer\Serializer\ArraySerializer;
use Orb\Serializer\SerializerRegistry;

class SerializerRegistryTest extends \DpUnitTestCase
{
	public function testAFullSerializationFlowArrayToArray()
	{
		$serializer = new SerializerRegistry(
			array(
				new ArraySerializer()
			)
		);

		$arr = array('data', 'in' => 'here');
		$this->assertEquals($arr, $serializer->serialize($arr));
	}

	public function testSerializerRegistryCallsFirstSupportedSerializer()
	{
		$registry = new SerializerRegistry();

		$data = $view = $format = 'string';

		$serializer1 = \Mockery::mock('Orb\Serializer\SerializerInterface');
		$serializer1->shouldReceive('supports')->with($data, $view, $format)->andReturn(false);

		$serializer2 = \Mockery::mock('Orb\Serializer\SerializerInterface');
		$serializer2->shouldReceive('supports')->with($data, $view, $format)->andReturn(true);
		$serializer2->shouldReceive('serialize')->with($data, $view, $format)->andReturn(array('string'));

		$registry->addSerializer($serializer1);
		$registry->addSerializer($serializer2);

		$result = $registry->serialize($data, $view, $format);

		$this->assertEquals(array('string'), $result);
	}
}
