<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings;

class ServerSettings
{
    /**
     * @var Settings
     */
    private $settings;

    /** @var string */
    public $cookie_path = '/';
    /** @var string */
    public $cookie_domain = '';
    /** @var bool */
    public $disable_csp_headers;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->resetSettings();
    }

    /**
     * Resets settings based on stored values.
     */
    public function resetSettings()
    {
        $this->cookie_path = $this->settings->get('core.cookie_path');
        if ($this->cookie_path === null) {
            $this->cookie_path = '/';
        }

        $this->cookie_domain = $this->settings->get('core.cookie_domain');
        if ($this->cookie_domain === null) {
            $this->cookie_domain = '';
        }

        $this->disable_csp_headers = $this->settings->get('core.disable_csp_headers');
        if ($this->disable_csp_headers === null) {
            $this->disable_csp_headers = false;
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $exportSettings = [
            'cookie_path'         => $this->cookie_path,
            'cookie_domain'       => $this->cookie_domain,
            'disable_csp_headers' => (bool) $this->disable_csp_headers,
        ];

        return $exportSettings;
    }

    /**
     * @param array $set_settings
     */
    public function setArray(array $set_settings)
    {
        foreach ($set_settings as $s => $val) {
            if (property_exists($this, $s)) {
                $this->$s = $val;
            }
        }
    }

    /**
     * Persists settings.
     */
    public function saveSettings()
    {
        $this->settings->setSetting('core.cookie_path', $this->cookie_path);
        $this->settings->setSetting('core.cookie_domain', $this->cookie_domain);
        $this->settings->setSetting('core.disable_csp_headers', $this->disable_csp_headers);
    }
}
