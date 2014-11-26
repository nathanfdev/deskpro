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

use Orb\Sms\SmsSender;
use DpTestingMocks\SmsNullProvider;
use Orb\Sms\SmsMessage;

class SmsSenderTest extends \DpUnitTestCase
{
    public function testDefaultProvider()
    {
        $smsSender = new SmsSender();
        $this->assertNull($smsSender->getDefaultProvider(), 'default provider starts as null');

        $smsSender->setDefaultProvider($prov = new SmsNullProvider());
        $this->assertSame($prov, $smsSender->getDefaultProvider(), 'default provider can be set');
        $this->assertNotSame(new SmsNullProvider(), $smsSender->getDefaultProvider());
    }

    public function testSendUsesDefaults()
    {
        $sms = new SmsSender();
        $sms->setDefaultFromNumber($from = '+12345678901');
        $to = '1029384765';
        $text = new SmsMessage('Some text message!');

        $sms_provider = \Mockery::mock('Orb\Sms\SmsProviderInterface');
        $sms_provider->shouldReceive('sendMessage')->with($to, \Mockery::type('Orb\Sms\SmsMessageChunk'), $from)->once();

        $sms->setDefaultProvider($sms_provider);
        $sms->send($to, $text);
    }

    public function testSendPrefersPassedFromNumberOverDefault()
    {
        $sms = new SmsSender();
        $sms->setDefaultFromNumber($from = '+12345678901');
        $to = '1029384765';
        $text = new SmsMessage('Some text message!');

        $passed_from = '+11223344556';
        $sms_provider = \Mockery::mock('Orb\Sms\SmsProviderInterface');
        $sms_provider->shouldReceive('sendMessage')->with($to, \Mockery::type('Orb\Sms\SmsMessageChunk'), $passed_from)->once();
        $sms->setDefaultProvider($sms_provider);

        $sms->send($to, $text, $passed_from);
    }

    public function testSendPrefersPassedProviderOverDefault()
    {
        $sms = new SmsSender();
        $sms->setDefaultFromNumber($from = '+12345678901');
        $to = '1029384765';
        $text = new SmsMessage('Some text message!');

        $sms_provider = \Mockery::mock('Orb\Sms\SmsProviderInterface');
        $sms->setDefaultProvider($sms_provider);

        $passed_sms_provider = \Mockery::mock('Orb\Sms\SmsProviderInterface');
        $passed_sms_provider->shouldReceive('sendMessage')->with($to, \Mockery::type('Orb\Sms\SmsMessageChunk'), $from)->once();

        $sms->send($to, $text, null, $passed_sms_provider);
    }

    public function testExceptionOnNoProvider()
    {
        $smsSender = new SmsSender();
        $smsSender->setDefaultFromNumber('0099009090');

        $this->setExpectedException('Orb\Sms\SmsException');

        $smsSender->send('0099009909', new SmsMessage('Message!'));
    }
}
