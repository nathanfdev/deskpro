<?php

/**
 * DeskPRO.
 */

namespace DpTest\Orb\Sms;

use DpTest\DeskProTestCase;
use Orb\Sms\SmsMessage;
use Orb\Sms\SmsResult;

class SmsMessageTest extends DeskProTestCase
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
        $chunks  = $message->getChunks();
        $chunk1  = $chunks[0];
        $chunk2  = $chunks[1];
        $chunk3  = $chunks[2];

        $chunk1->setResult(new SmsResult(SmsResult::SMS_SENT, '90293029', '9020290', 'text', 'twilio', []));
        $chunk2->setResult(new SmsResult(SmsResult::SMS_SENT, '90293029', '9020290', 'text', 'twilio', []));
        $chunk3->setResult(new SmsResult(SmsResult::SMS_SENT, '90293029', '9020290', 'text', 'twilio', []));

        $this->assertTrue($message->isSent());
    }

    public function testMessageIsNotSentIfAnyChunkIsNotSent()
    {
        $message = new SmsMessage(str_repeat('ten chars ', 34));
        $chunks  = $message->getChunks();
        $chunk1  = $chunks[0];
        $chunk2  = $chunks[1];
        $chunk3  = $chunks[2];

        $chunk1->setResult(new SmsResult(SmsResult::SMS_SENT, '90293029', '9020290', 'text', 'twilio', []));
        $chunk2->setResult(new SmsResult(SmsResult::SMS_FAIL, '90293029', '9020290', 'text', 'twilio', []));
        $chunk3->setResult(new SmsResult(SmsResult::SMS_SENT, '90293029', '9020290', 'text', 'twilio', []));

        $this->assertFalse($message->isSent());
    }
}
