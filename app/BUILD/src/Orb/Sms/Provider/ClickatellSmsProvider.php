<?php

namespace Orb\Sms\Provider;

use Orb\Sms\Provider\ClickatellRest as Rest;
use Orb\Sms\SmsMessageChunk;
use Orb\Sms\SmsProviderInterface;
use Orb\Sms\SmsResult;

/**
 * Class ClickatellSmsProvider.
 */
class ClickatellSmsProvider implements SmsProviderInterface
{
    /**
     * @var Rest
     */
    private $client;

    /**
     * @var string
     */
    private $authToken;

    /**
     * @param string $authToken
     */
    public function __construct($authToken)
    {
        $this->client    = new Rest($authToken);
        $this->authToken = $authToken;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage($toNumber, SmsMessageChunk $chunk, $fromNumber)
    {
        $text = $chunk->getText();

        try {
            $result = $this->client->sendMessage([
                'to'      => [$toNumber],
                'content' => $text,
            ]);

            if (isset($result[0])) {
                $message = $result[0];
                if ($message['accepted']) {
                    return new SmsResult(SmsResult::SMS_SENT, $fromNumber, $toNumber, $text, $this->getName(), [$message['apiMessageId']]);
                } else {
                    return new SmsResult(SmsResult::SMS_FAIL, $fromNumber, $toNumber, $text, $this->getName(), [$message['apiMessageId']]);
                }
            } else {
                return new SmsResult(SmsResult::SMS_FAIL, $fromNumber, $toNumber, $text, $this->getName(), [
                    'status' => 'empty_response',
                ]);
            }
        } catch (\Exception $e) {
            return new SmsResult(SmsResult::SMS_FAIL, $fromNumber, $toNumber, $text, $this->getName(), [
                'status'  => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'clickatell';
    }

    /**
     * {@inheritdoc}
     */
    public function getParams()
    {
        return [
            'auth_token' => $this->authToken,
        ];
    }
}
