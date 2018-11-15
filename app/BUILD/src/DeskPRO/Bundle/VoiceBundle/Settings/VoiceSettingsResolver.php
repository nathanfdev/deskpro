<?php

namespace DeskPRO\Bundle\VoiceBundle\Settings;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\Model\VoiceSettings;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;

/**
 * Class VoiceSettingsResolver.
 */
class VoiceSettingsResolver
{
    const VOICE_AGENT_VOICEMAIL_TIMEOUT           = 'voice.agent_voicemail_timeout';
    const VOICE_GROUP_MISSED_CALL_TICKETS         = 'voice.group_missed_call_tickets';
    const VOICE_GROUP_MISSED_CALL_TICKETS_TIMEOUT = 'voice.group_missed_call_tickets_timeout';
    const VOICE_TWILIO_PROXY_API_URL              = 'voice.twilio_proxy_api_url';
    const VOICE_TWILIO_PROXY_TASK_ROUTER_URL      = 'voice.twilio_proxy_task_router_url';
    const VOICE_TWILIO_PROXY_ACCOUNTS_URL         = 'voice.twilio_proxy_accounts_url';
    const VOICE_TWILIO_PROXY_PRICING_URL          = 'voice.twilio_proxy_pricing_url';
    const VOICE_PLIVO_PROXY_HOST                  = 'voice.plivo_proxy_host';
    const VOICE_PLIVO_PROXY_USERNAME              = 'voice.plivo_proxy_username';
    const VOICE_PLIVO_PROXY_PASSWORD              = 'voice.plivo_proxy_password';

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(SettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @return VoiceSettings
     */
    public function getVoiceSettings()
    {
        $model = new VoiceSettings();
        $model
            ->setAgentVoicemailTimeout($this->getAgentVoicemailTimeout())
            ->setGroupMissedCallTickets($this->isGroupMissedCallTickets())
            ->setGroupMissedCallTicketsTimeout($this->getGroupMissedCallTicketsTimeout())
        ;

        return $model;
    }

    /**
     * @return int
     */
    public function getAgentVoicemailTimeout()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_AGENT_VOICEMAIL_TIMEOUT, TwilioAdapter::VOICEMAIL_WAITING_TIMEOUT);
    }

    /**
     * @return bool
     */
    public function isGroupMissedCallTickets()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_GROUP_MISSED_CALL_TICKETS);
    }

    /**
     * @return int
     */
    public function getGroupMissedCallTicketsTimeout()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_GROUP_MISSED_CALL_TICKETS_TIMEOUT);
    }

    /**
     * @return string|null
     */
    public function getTwilioProxyApiUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_TWILIO_PROXY_API_URL);
    }

    /**
     * @return string|null
     */
    public function getTwilioProxyTaskRouterUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_TWILIO_PROXY_TASK_ROUTER_URL);
    }

    /**
     * @return string|null
     */
    public function getTwilioProxyAccountsUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_TWILIO_PROXY_ACCOUNTS_URL);
    }

    /**
     * @return string|null
     */
    public function getTwilioProxyPricingUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_TWILIO_PROXY_PRICING_URL);
    }

    /**
     * @return string|null
     */
    public function getPlivoProxyHost()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_PLIVO_PROXY_HOST);
    }

    /**
     * @return string|null
     */
    public function getPlivoProxyUsername()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_PLIVO_PROXY_USERNAME);
    }

    /**
     * @return string|null
     */
    public function getPlivoProxyPassword()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_PLIVO_PROXY_PASSWORD);
    }
}
