<?php

/**
 * Orb.
 */

namespace Orb\Sms\Provider;

use Orb\Service\Twilio\Twilio;
use Orb\Sms\SmsException;
use Orb\Sms\SmsMessageChunk;
use Orb\Sms\SmsProviderInterface;
use Orb\Sms\SmsResult;
use Orb\Util\PhoneNumbers;

class TwilioSmsProvider implements SmsProviderInterface
{
    /**
     * @var Twilio our Twilio service
     */
    protected $twilio;

    /**
     * @var string
     */
    protected $sid;

    /**
     * @var string
     */
    protected $auth_token;

    public function __construct($sid, $auth_token)
    {
        $this->sid        = $sid;
        $this->auth_token = $auth_token;
        $this->twilio     = new Twilio($sid, $auth_token);
    }

    /**
     * @return string a friendly name for the account
     */
    public function getAccountName()
    {
        return $this->twilio->getFriendlyName();
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage($toNumber, SmsMessageChunk $chunk, $fromNumber)
    {
        $textMessage = $chunk->getText();

        try {
            $message = $this->twilio->sendSms($toNumber, $textMessage, $fromNumber);
        } catch (\Services_Twilio_RestException $e) {
            $result = new SmsResult(
                SmsResult::SMS_FAIL, $fromNumber, $toNumber, $textMessage, $this->getName(), [
                    'status'        => $e->getCode(),
                    'message'       => $e->getMessage(),
                    'twilio_status' => $e->getStatus(),
                    'twilio_info'   => $e->getInfo(),
                ]
            );
            $result->setProviderMessage($e->getStatus().' - '.$e->getMessage().' ('.$e->getCode().')');

            return $result;
        } catch (\Exception $e) {
            $result = new SmsResult(
                SmsResult::SMS_FAIL, $fromNumber, $toNumber, $textMessage, $this->getName(), [
                    'status'  => $e->getCode(),
                    'message' => $e->getMessage(),
                ]
            );
            $result->setProviderMessage($e->getCode().' - '.$e->getMessage());

            return $result;
        }

        // we successfully sent a valid SMS to Twilio
        $result = new SmsResult(
            SmsResult::SMS_SENT, $fromNumber, $toNumber, $textMessage, $this->getName(), [
                'sid'             => $message->sid, // this can later be used to find the status of the sms
                'num_segments'    => $message->num_segments,
                'provider_status' => $message->status,
            ]
        );

        return $result;
    }

    /**
     * @throws \Orb\Sms\SmsException
     *
     * @return array an array of arrays in the format:
     *               array( 'display_name' => 'Some Name', 'phone_number' => '+19023340390 )
     */
    public function getIncomingNumbers()
    {
        try {
            $out = [];

            $numbers = $this->twilio->getIncomingNumbers();
            foreach ($numbers as $display => $number) {
                $number = PhoneNumbers::toInternationalFormat($number);
                $out[]  = ['display_name' => $display, 'number' => $number];
            }

            return $out;
        } catch (\Exception $e) {
            throw new SmsException('could not get incoming numbers from Twilio provider');
        }
    }

    public function setUrlForNumber($twilio_endpoint, $number)
    {
        $this->twilio->setUrlForNumber($twilio_endpoint, $number);
    }

    /**
     * A string identifier of the provider. This should be unique across the system.
     *
     * @return string
     */
    public function getName()
    {
        return 'twilio';
    }

    /**
     * {@inheritdoc}
     */
    public function getParams()
    {
        return [
            'sid'        => $this->sid,
            'auth_token' => $this->auth_token,
        ];
    }
}
