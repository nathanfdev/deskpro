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

use DpTestingMocks\SmsNullProvider;
use Orb\Sms\SmsResult;

class SmsResultTest extends \DpUnitTestCase
{
	public function testStatus()
	{
		$result = $this->createSmsRssult();
		$this->assertEquals(SmsResult::SMS_SENT, $result->getStatus());

		$result->setStatus(SmsResult::SMS_FAIL);
		$this->assertEquals(SmsResult::SMS_FAIL, $result->getStatus());
	}

	public function testStatusValid()
	{
		$result = $this->createSmsRssult();

		$this->setExpectedException('\InvalidArgumentException');
		$result->setStatus('some non-existent status code');
	}

	public function testIsSent()
	{
		$result = $this->createSmsRssult();

		$result->setStatus(SmsResult::SMS_SENT);
		$this->assertTrue($result->isSent());

		$result->setStatus(SmsResult::SMS_FAIL);
		$this->assertFalse($result->isSent());
	}

	public function testIsFail()
	{
		$result = $this->createSmsRssult();

		$result->setStatus(SmsResult::SMS_FAIL);
		$this->assertTrue($result->isFail());

		$result->setStatus(SmsResult::SMS_SENT);
		$this->assertFalse($result->isFail());
	}

	/**
	 * @return SmsResult
	 */
	protected function createSmsRssult()
	{
		return new SmsResult(SmsResult::SMS_SENT, '9988998899', '1234567890', 'test message', 'null', array());
	}
}
