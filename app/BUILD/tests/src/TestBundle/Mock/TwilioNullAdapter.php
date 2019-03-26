<?php

namespace DpTestSrc\TestBundle\Mock;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use Twilio\Rest\Api\V2010;
use Twilio\Rest\Api\V2010\Account\ApplicationInstance;
use Twilio\Rest\Client;
use Twilio\Rest\Taskrouter;

/**
 * Class TwilioNullAdapter.
 */
class TwilioNullAdapter extends TwilioAdapter
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAccount(TwilioVoiceAccount $account)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function buyNumber(TwilioVoiceAccount $account, array $data)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setTwimlAppId(VoiceNumber $number)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function unsetTwimlAppId(VoiceNumber $number)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createTwimlApp(TwilioVoiceAccount $account, $requestUrl, $voiceMethod, $statusUrl, $statusMethod)
    {
        $payload = [
            'sid'                     => 'sid',
            'account_sid'             => 'account_sid',
            'api_version'             => 'api_version',
            'date_created'            => 'date_created',
            'date_updated'            => 'date_updated',
            'friendly_name'           => 'friendly_name',
            'message_status_callback' => 'message_status_callback',
            'sms_fallback_method'     => 'sms_fallback_method',
            'sms_fallback_url'        => 'sms_fallback_url',
            'sms_method'              => 'sms_method',
            'sms_status_callback'     => 'sms_status_callback',
            'sms_url'                 => 'sms_url',
            'status_callback'         => 'status_callback',
            'status_callback_method'  => 'status_callback_method',
            'uri'                     => 'uri',
            'voice_caller_id_lookup'  => 'voice_caller_id_lookup',
            'voice_fallback_method'   => 'voice_fallback_method',
            'voice_fallback_url'      => 'voice_fallback_url',
            'voice_method'            => 'voice_method',
            'voice_url'               => 'voice_url',
        ];

        return new ApplicationInstance($this->getVersion(), $payload, 'account_sid');
    }

    /**
     * {@inheritdoc}
     */
    public function createPhoneToken(TwilioVoiceAccount $account, Person $person)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConference(TwilioVoiceAccount $account, $conferenceSid)
    {
        $payload = [
            'account_sid'      => 'account_sid',
            'date_created'     => 'date_created',
            'date_updated'     => 'date_updated',
            'api_version'      => 'api_version',
            'friendly_name'    => 'friendly_name',
            'region'           => 'region',
            'sid'              => 'sid',
            'status'           => 'status',
            'uri'              => 'uri',
            'subresource_uris' => 'subresource_uris',
        ];

        return new V2010\Account\ConferenceInstance($this->getVersion(), $payload, 'account_sid');
    }

    /**
     * {@inheritdoc}
     */
    public function holdConferenceEndUser(VoicePhoneCall $phoneCall, $isHold, array $params = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function tryEndConference(VoicePhoneCall $phoneCall)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveAgentPhoneCallParticipants(VoicePhoneCall $phoneCall)
    {
        return [];
    }

    /**
     * @return V2010
     */
    private function getVersion()
    {
        return new V2010(new Taskrouter(new Client('username', 'password')));
    }
}
