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
}
