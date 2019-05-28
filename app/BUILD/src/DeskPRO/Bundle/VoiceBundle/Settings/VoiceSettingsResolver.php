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
    const VOICE_AGENT_DEFAULT_DEPARTMENT          = 'voice.agent_default_department';
    const VOICE_AGENT_DEFAULT_BRAND               = 'voice.agent_default_brand';
    const VOICE_GROUP_MISSED_CALL_TICKETS         = 'voice.group_missed_call_tickets';
    const VOICE_GROUP_MISSED_CALL_TICKETS_TIMEOUT = 'voice.group_missed_call_tickets_timeout';
    const VOICE_FORWARDING_MACHINE_DETECTION      = 'voice.forwarding_machine_detection';
    const VOICE_FORWARDING_NUMBER_TYPE            = 'voice.forwarding_number_type';
    const VOICE_FORWARDING_NUMBER                 = 'voice.forwarding_number';
    const VOICE_TWILIO_PROXY_HOST                 = 'voice.twilio_proxy_host';
    const VOICE_TWILIO_PROXY_USERNAME             = 'voice.twilio_proxy_username';
    const VOICE_TWILIO_PROXY_PASSWORD             = 'voice.twilio_proxy_password';
    const VOICE_TWILIO_PROXY_TASK_ROUTER_HOST     = 'voice.twilio_proxy_task_router_host';
    const VOICE_TWILIO_PROXY_ACCOUNTS_HOST        = 'voice.twilio_proxy_accounts_host';
    const VOICE_TWILIO_PROXY_PRICING_HOST         = 'voice.twilio_proxy_pricing_host';
    const VOICE_PLIVO_PROXY_HOST                  = 'voice.plivo_proxy_host';
    const VOICE_PLIVO_PROXY_USERNAME              = 'voice.plivo_proxy_username';
    const VOICE_PLIVO_PROXY_PASSWORD              = 'voice.plivo_proxy_password';

    const DEFAULT_FORWARDING_NUMBER  = 'default';
    const SPECIFIC_FORWARDING_NUMBER = 'specific';

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
            ->setAgentDefaultDepartment($this->getAgentDefaultDepartment())
            ->setAgentDefaultBrand($this->getAgentDefaultBrand())
            ->setAgentVoicemailTimeout($this->getAgentVoicemailTimeout())
            ->setGroupMissedCallTickets($this->isGroupMissedCallTickets())
            ->setGroupMissedCallTicketsTimeout($this->getGroupMissedCallTicketsTimeout())
            ->setForwardingMachineDetection($this->getForwardingMachineDetection())
            ->setForwardingNumberType($this->getForwardingNumberType())
            ->setForwardingNumber($this->getForwardingNumber())
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
     * @return int
     */
    public function getAgentDefaultDepartment()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_AGENT_DEFAULT_DEPARTMENT);
    }

    /**
     * @return int
     */
    public function getAgentDefaultBrand()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_AGENT_DEFAULT_BRAND);
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
     * @return bool
     */
    public function getForwardingMachineDetection()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_FORWARDING_MACHINE_DETECTION);
    }

    /**
     * @return string
     */
    public function getForwardingNumberType()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_FORWARDING_NUMBER_TYPE);
    }

    /**
     * @return int
     */
    public function getForwardingNumber()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_FORWARDING_NUMBER);
    }

    /**
     * @return string|null
     */
    public function getTwilioProxyApiUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_TWILIO_PROXY_HOST);
    }

    /**
     * @return string|null
     */
    public function getTwilioProxyUsername()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_TWILIO_PROXY_USERNAME);
    }

    /**
     * @return string|null
     */
    public function getTwilioProxyPassword()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_TWILIO_PROXY_PASSWORD);
    }

    /**
     * @return string|null
     */
    public function getTwilioProxyTaskRouterUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_TWILIO_PROXY_TASK_ROUTER_HOST);
    }

    /**
     * @return string|null
     */
    public function getTwilioProxyAccountsUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_TWILIO_PROXY_ACCOUNTS_HOST);
    }

    /**
     * @return string|null
     */
    public function getTwilioProxyPricingUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_TWILIO_PROXY_PRICING_HOST);
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
