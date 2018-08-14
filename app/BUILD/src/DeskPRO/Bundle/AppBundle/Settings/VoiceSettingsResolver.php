<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\Model\VoiceSettings;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;

/**
 * Class VoiceSettingsResolver.
 */
class VoiceSettingsResolver
{
    const VOICE_AGENT_VOICEMAIL_TIMEOUT = 'voice.agent_voicemail_timeout';
    const VOICE_PROXY_API_URL           = 'voice.proxy_api_url';
    const VOICE_PROXY_TASK_ROUTER_URL   = 'voice.proxy_task_router_url';
    const VOICE_PROXY_ACCOUNTS_URL      = 'voice.proxy_accounts_url';
    const VOICE_PROXY_USERNAME          = 'voice.proxy_username';
    const VOICE_PROXY_PASSWORD          = 'voice.proxy_password';

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
        $settings = $this->settingsResolver->getGlobalSettings();

        $model = new VoiceSettings();
        $model->setAgentVoicemailTimeout($settings->get(self::VOICE_AGENT_VOICEMAIL_TIMEOUT, TwilioAdapter::VOICEMAIL_WAITING_TIMEOUT));

        return $model;
    }

    /**
     * @return string|null
     */
    public function getProxyApiUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_PROXY_API_URL);
    }

    /**
     * @return string|null
     */
    public function getProxyTaskRouterUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_PROXY_TASK_ROUTER_URL);
    }

    /**
     * @return string|null
     */
    public function getProxyAccountsUrl()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_PROXY_ACCOUNTS_URL);
    }

    /**
     * @return string|null
     */
    public function getProxyUsername()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_PROXY_USERNAME);
    }

    /**
     * @return string|null
     */
    public function getProxyPassword()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::VOICE_PROXY_PASSWORD);
    }
}
