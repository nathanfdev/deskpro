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

namespace DpUnitTests\Orb\Util;

use Orb\Util\PhoneNumbers;

class PhoneNumbersTest extends \DpUnitTestCase
{
	public function testType()
	{
		// note how US/Canada (and many countries) make it impossible to
		// distinguish between mobile/landline by looking only at the number
		$this->assertEquals('landline-or-mobile', PhoneNumbers::getType('+19024038712'));
		$this->assertEquals('landline-or-mobile', PhoneNumbers::getType('+16617480241'));
		$this->assertEquals('mobile', PhoneNumbers::getType('+8109090908989'));
		$this->assertEquals('toll-free', PhoneNumbers::getType('+18009835929'));
		$this->assertEquals('unknown', PhoneNumbers::getType('+814590908989'));
	}

	public function testRegion()
	{
		$this->assertEquals('CA', PhoneNumbers::getRegionForNumber('+19024334909'));
		$this->assertEquals('PR', PhoneNumbers::getRegionForNumber('+17879920947'));
		$this->assertEquals('BS', PhoneNumbers::getRegionForNumber('+12424459909'));
		$this->assertEquals('NO', PhoneNumbers::getRegionForNumber('+4799872329'));
		$this->assertEquals('GB', PhoneNumbers::getRegionForNumber('+441224451909'));
		$this->assertEquals('US', PhoneNumbers::getRegionForNumber('+16174530990'));
		$this->assertEquals('BR', PhoneNumbers::getRegionForNumber('+55918930093'));
		$this->assertEquals('RU', PhoneNumbers::getRegionForNumber('+74718790092'));
		$this->assertEquals('CN', PhoneNumbers::getRegionForNumber('+869153450959'));
		$this->assertEquals('ZA', PhoneNumbers::getRegionForNumber('+27110982345'));
		$this->assertEquals('JP', PhoneNumbers::getRegionForNumber('+816690908989'));
	}
}
