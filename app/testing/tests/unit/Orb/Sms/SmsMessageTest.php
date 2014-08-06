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
 * @subpackage Sms
 */

namespace DpUnitTests\Sms;

use Orb\Sms\SmsMessage;

class SmsMessageTest extends \DpUnitTestCase
{
	public function testChunks()
	{
		$msg = 'ten chars ';

		$message = new SmsMessage(str_repeat($msg, 17));

		$this->assertTrue($message->hasMultipleChunks());
		$this->assertCount(2, $message->getChunks());
		$chunks = $message->getChunks();
		$this->assertEquals(trim(str_repeat($msg, 16)), $chunks[0]);
		$this->assertEquals(trim(str_repeat($msg, 1)), $chunks[1]);
	}

	public function testNoChunks()
	{
		$msg = 'ten chars ';

		$message = new SmsMessage(str_repeat($msg, 16));

		$this->assertFalse($message->hasMultipleChunks());
		$this->assertCount(1, $message->getChunks());
		$chunks = $message->getChunks();
		$this->assertEquals(trim(str_repeat($msg, 16)), $chunks[0]);
	}
}
