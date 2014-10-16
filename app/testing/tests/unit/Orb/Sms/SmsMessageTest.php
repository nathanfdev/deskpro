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
 * @subpackage Sms
 */

namespace DpUnitTests\Sms;

use Orb\Sms\SmsMessage;
use Orb\Sms\SmsMessageChunk;
use Orb\Sms\SmsResult;

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


	public function testMessageChunkOrder()
	{
		$msg = '
			The beginning of this message starts a little bit like this, and we have to allow for 160 characters inside of it.  Once we get past 160 characters, we cut. There, this should be chunk two.
			';

		$message = new SmsMessage($msg);

		$chunks = $message->getChunks();
		$chunk1 = $chunks[0];
		$chunk2 = $chunks[1];

		$this->assertEquals(
			'The beginning of this message starts a little bit like this, and we have to allow for 160 characters inside of it.  Once we get past 160 characters, we cut.',
			$chunk1->getText()
		);

		$this->assertEquals(
			'There, this should be chunk two.',
			$chunk2->getText()
		);
	}


	public function testMessageIsSentIfAllChunksSent()
	{
		$message = new SmsMessage(str_repeat('ten chars ', 34));
		$chunks = $message->getChunks();
		$chunk1 = $chunks[0];
		$chunk2 = $chunks[1];
		$chunk3 = $chunks[2];

		$chunk1->setResult(new SmsResult(SmsResult::SMS_SENT, '90293029', '9020290', 'text', 'twilio', array()));
		$chunk2->setResult(new SmsResult(SmsResult::SMS_SENT, '90293029', '9020290', 'text', 'twilio', array()));
		$chunk3->setResult(new SmsResult(SmsResult::SMS_SENT, '90293029', '9020290', 'text', 'twilio', array()));

		$this->assertTrue($message->isSent());
	}


	public function testMessageIsNotSentIfAnyChunkIsNotSent()
	{
		$message = new SmsMessage(str_repeat('ten chars ', 34));
		$chunks = $message->getChunks();
		$chunk1 = $chunks[0];
		$chunk2 = $chunks[1];
		$chunk3 = $chunks[2];

		$chunk1->setResult(new SmsResult(SmsResult::SMS_SENT, '90293029', '9020290', 'text', 'twilio', array()));
		$chunk2->setResult(new SmsResult(SmsResult::SMS_FAIL, '90293029', '9020290', 'text', 'twilio', array()));
		$chunk3->setResult(new SmsResult(SmsResult::SMS_SENT, '90293029', '9020290', 'text', 'twilio', array()));

		$this->assertFalse($message->isSent());
	}
}
