<?php

namespace DpTestSrc\TestBundle\Mock;

use Orb\Sms\SmsMessageChunk;
use Orb\Sms\SmsProviderInterface;
use Orb\Sms\SmsResult;

class SmsNullProvider implements SmsProviderInterface
{
    public function sendMessage($toNumber, SmsMessageChunk $textMessage, $fromNumber)
    {
        return new SmsResult(SmsResult::SMS_SENT, $fromNumber, $toNumber, $textMessage, $this, []);
    }

    public function getName()
    {
        return 'null';
    }

    public function getParams()
    {
        return [];
    }
}
