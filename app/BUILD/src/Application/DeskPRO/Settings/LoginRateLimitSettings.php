<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings;

class LoginRateLimitSettings
{
    const KEY = 'login_rate_limit';

    /**
     * @var \Application\DeskPRO\Settings\Settings
     */
    private $settings;
    /** @var string */
    private $context;

    /** @var bool */
    public $enabled;
    /** @var int */
    public $attempts;
    /** @var int */
    public $attempts_time;
    /** @var int */
    public $lock_time;

    /**
     * @param Settings $settings
     * @param string   $context
     *
     * @throws \Exception
     */
    public function __construct(Settings $settings, $context = 'user')
    {
        if ('user' !== $context && 'agent' !== $context) {
            throw new \Exception(sprintf('Wrong context "%s"', $context));
        }
        $this->settings = $settings;
        $this->context  = $context;

        $this->resetSettings();
    }

    /**
     * Resets settings based on stored values.
     */
    public function resetSettings()
    {
        $this->enabled       = (bool) $this->settings->get($this->context.'.'.self::KEY.'.enabled');
        $this->attempts      = (int) $this->settings->get($this->context.'.'.self::KEY.'.attempts');
        $this->attempts_time = (int) $this->settings->get($this->context.'.'.self::KEY.'.attempts_time');
        $this->lock_time     = (int) $this->settings->get($this->context.'.'.self::KEY.'.lock_time');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $ret = [];

        foreach ([
                     'enabled',
                     'attempts',
                     'attempts_time',
                     'lock_time',
                 ] as $opt) {
            $ret[$opt] = $this->$opt;
        }

        return $ret;
    }

    /**
     * @param array $set_settings
     */
    public function setArray(array $set_settings)
    {
        $this->enabled       = (bool) $set_settings['enabled'];
        $this->attempts      = (int) $set_settings['attempts'];
        $this->attempts_time = (int) $set_settings['attempts_time'];
        $this->lock_time     = (int) $set_settings['lock_time'];
    }

    /**
     * Persists settings.
     */
    public function saveSettings()
    {
        foreach ([
                     'enabled',
                     'attempts',
                     'attempts_time',
                     'lock_time',
                 ] as $opt) {
            $this->settings->setSetting($this->context.'.'.self::KEY.'.'.$opt, $this->$opt);
        }
    }
}
