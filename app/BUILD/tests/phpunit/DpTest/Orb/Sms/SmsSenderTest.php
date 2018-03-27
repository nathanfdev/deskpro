<?php

/**
 * DeskPRO.
 */

namespace DpTest\Orb\Sms;

use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\SmsNullProvider;
use Orb\Sms\SmsMessage;
use Orb\Sms\SmsSender;

class SmsSenderTest extends DeskProTestCase
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
        $to   = '1029384765';
        $text = new SmsMessage('Some text message!');

        $sms_provider = \Mockery::mock('Orb\Sms\SmsProviderInterface');
        $sms_provider->shouldReceive('sendMessage')->with($to, \Mockery::type('Orb\Sms\SmsMessageChunk'), $from)->once(
        );

        $sms->setDefaultProvider($sms_provider);
        $sms->send($to, $text);
    }

    public function testSendPrefersPassedFromNumberOverDefault()
    {
        $sms = new SmsSender();
        $sms->setDefaultFromNumber($from = '+12345678901');
        $to   = '1029384765';
        $text = new SmsMessage('Some text message!');

        $passed_from  = '+11223344556';
        $sms_provider = \Mockery::mock('Orb\Sms\SmsProviderInterface');
        $sms_provider->shouldReceive('sendMessage')->with(
            $to,
            \Mockery::type('Orb\Sms\SmsMessageChunk'),
            $passed_from
        )->once();
        $sms->setDefaultProvider($sms_provider);

        $sms->send($to, $text, $passed_from);
    }

    public function testSendPrefersPassedProviderOverDefault()
    {
        $sms = new SmsSender();
        $sms->setDefaultFromNumber($from = '+12345678901');
        $to   = '1029384765';
        $text = new SmsMessage('Some text message!');

        $sms_provider = \Mockery::mock('Orb\Sms\SmsProviderInterface');
        $sms->setDefaultProvider($sms_provider);

        $passed_sms_provider = \Mockery::mock('Orb\Sms\SmsProviderInterface');
        $passed_sms_provider->shouldReceive('sendMessage')->with(
            $to,
            \Mockery::type('Orb\Sms\SmsMessageChunk'),
            $from
        )->once();

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
