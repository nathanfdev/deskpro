<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\TwitterSetup;

class TwitterSetup
{
    /**
     * @var \Application\DeskPRO\Settings\Settings
     */
    private $settings;

    /** @var string */
    public $twitter_agent_consumer_key = '';
    /** @var string */
    public $twitter_agent_consumer_secret = '';
    /** @var string */
    public $twitter_user_consumer_key = '';
    /** @var string */
    public $twitter_user_consumer_secret = '';
    /** @var int */
    public $twitter_auto_remove_time = 1209600;

    /**
     * @param \Application\DeskPRO\Settings\Settings $settings
     */
    public function __construct(\Application\DeskPRO\Settings\Settings $settings)
    {
        $this->settings = $settings;
        $this->resetTwitterSetup();
    }

    /**
     * Resets twitter setup based on stored values.
     */
    public function resetTwitterSetup()
    {
        $this->twitter_agent_consumer_key    = $this->settings->get('core.twitter_agent_consumer_key');
        $this->twitter_agent_consumer_secret = $this->settings->get('core.twitter_agent_consumer_secret');
        $this->twitter_user_consumer_key     = $this->settings->get('core.twitter_user_consumer_key');
        $this->twitter_user_consumer_secret  = $this->settings->get('core.twitter_user_consumer_secret');
        $this->twitter_auto_remove_time      = $this->settings->get('core.twitter_auto_remove_time');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $export_settings = [];

        foreach (
            [
                'twitter_agent_consumer_key',
                'twitter_agent_consumer_secret',
                'twitter_user_consumer_key',
                'twitter_user_consumer_secret',
                'twitter_auto_remove_time',
            ] as $s) {
            $export_settings[$s] = $this->$s;
        }

        return $export_settings;
    }

    /**
     * @param array $new_values
     */
    public function setArray(array $new_values)
    {
        foreach ($new_values as $v => $val) {
            if (property_exists($this, $v)) {
                $this->$v = $val;
            }
        }
    }

    /**
     * Persists twitter setup.
     */
    public function saveTwitterSetup()
    {
        $this->settings->setSetting('core.twitter_agent_consumer_key', $this->twitter_agent_consumer_key);
        $this->settings->setSetting('core.twitter_agent_consumer_secret', $this->twitter_agent_consumer_secret);
        $this->settings->setSetting('core.twitter_user_consumer_key', $this->twitter_user_consumer_key);
        $this->settings->setSetting('core.twitter_user_consumer_secret', $this->twitter_user_consumer_secret);
        $this->settings->setSetting('core.twitter_auto_remove_time', (int) $this->twitter_auto_remove_time);
    }
}
