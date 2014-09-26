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

namespace DpUnitTests\DeskPRO\Sms;

use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\Sms\DeskPROSmsSender;
use Orb\Sms\SmsMessage;

class DeskPROSmsSenderTest extends \DpUnitTestCase
{
	public function testSendUsesDefaults()
	{
		$queue = $this->getMockJobQueue();
		$queue->shouldReceive('addJob')->once();

		$sms = new DeskPROSmsSender(null, null, $queue);
		$sms->setDefaultFromNumber($from = '+12345678901');
		$to = '1029384765';
		$text = new SmsMessage('Some text message!');

		$sms_provider = \Mockery::mock('Orb\Sms\SmsProviderInterface');
		$sms_provider->shouldReceive('getName')->andReturn('name');
		$sms_provider->shouldReceive('getParams')->andReturn(array());

		$sms->setDefaultProvider($sms_provider);

		$sms->send($to, $text);
	}

	public function testMaxChunks()
	{
		$sms = new DeskPROSmsSender(null, null, $this->getMockJobQueue(), 2);
		$sms->setDefaultFromNumber($from = '+12345678901');
		$to = '1029384765';
		$text = new SmsMessage(str_repeat('Some text message!', 50));

		$this->setExpectedException('Orb\Sms\SmsException');

		$sms->send($to, $text);
	}


	private function getMockJobQueue()
	{
		$queue = \Mockery::mock('Application\DeskPRO\JobQueue\JobQueue');

		return $queue;
	}
}
