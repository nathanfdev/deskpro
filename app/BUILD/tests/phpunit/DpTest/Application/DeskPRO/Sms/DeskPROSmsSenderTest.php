<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application;

use Application\DeskPRO\Sms\DeskPROSmsSender;
use DpTest\DeskProTestCase;
use Orb\Sms\SmsMessage;

class DeskPROSmsSenderTest extends DeskProTestCase
{
    public function testSendUsesDefaults()
    {
        $queue = $this->getMockJobQueue();
        $queue->shouldReceive('addJob')->once();

        $sms = new DeskPROSmsSender(null, null, $queue);
        $sms->setDefaultFromNumber($from = '+12345678901');
        $to   = '1029384765';
        $text = new SmsMessage('Some text message!');

        $sms_provider = \Mockery::mock('Orb\Sms\SmsProviderInterface');
        $sms_provider->shouldReceive('getName')->andReturn('name');
        $sms_provider->shouldReceive('getParams')->andReturn([]);

        $sms->setDefaultProvider($sms_provider);

        $sms->send($to, $text);
    }

    public function testMaxChunks()
    {
        $sms = new DeskPROSmsSender(null, null, $this->getMockJobQueue(), 2);
        $sms->setDefaultFromNumber($from = '+12345678901');
        $to   = '1029384765';
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
