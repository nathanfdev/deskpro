<?php

/**
 * DeskPRO.
 */

namespace DpTest\Orb\Sms;

use DpTest\DeskProTestCase;
use Orb\Sms\SmsMessageChunk;

class SmsMessageChunkTest extends DeskProTestCase
{
    public function testExceptionOnChunkTooBig()
    {
        $this->setExpectedException('Orb\Sms\SmsException');
        new SmsMessageChunk(str_repeat('ten chars ', 20));
    }
}
