<?php

/**
 * DeskPRO.
 */

namespace DpTest\Orb\Sms;

use DpTest\DeskProTestCase;
use Orb\Sms\SmsResult;

class SmsResultTest extends DeskProTestCase
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
        return new SmsResult(SmsResult::SMS_SENT, '9988998899', '1234567890', 'test message', 'null', []);
    }
}
