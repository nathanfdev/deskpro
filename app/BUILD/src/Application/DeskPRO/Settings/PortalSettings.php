<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings;

/**
 * Class PortalSettings.
 *
 * @deprecated
 */
class PortalSettings
{
    /**
     * @var Settings
     */
    private $settings;

    /** @var bool */
    public $portal_enabled;
    /** @var string */
    public $favicon_blob_url;
    /** @var int */
    public $favicon_blob_id;

    /** @var int */
    public $show_ratings;
    /** @var bool */
    public $publish_comments;

    /** @var bool */
    public $register_captcha;
    /** @var bool */
    public $publish_captcha;
    /** @var bool */
    public $always_show_captcha;

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
        $this->portal_enabled = (bool) $this->settings->get('user.portal_enabled');

        $this->favicon_blob_id  = (int) $this->settings->get('core.favicon_blob_id');
        $this->favicon_blob_url = $this->settings->get('core.favicon_blob_url') ?: null;

        $this->show_ratings     = (int) $this->settings->get('user.show_ratings');
        $this->publish_comments = (bool) $this->settings->get('user.publish_comments');

        // TODO these captcha changes were redone see settings.php 'captcha' section

        $this->register_captcha    = (bool) $this->settings->get('user.register_captcha');
        $this->publish_captcha     = (bool) $this->settings->get('user.publish_captcha');
        $this->always_show_captcha = (bool) $this->settings->get('user.always_show_captcha');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $export_settings = [
            'portal_enabled'      => $this->portal_enabled,
            'favicon_blob_id'     => $this->favicon_blob_id,
            'favicon_blob_url'    => $this->favicon_blob_url,
            'show_ratings'        => $this->show_ratings,
            'publish_comments'    => $this->publish_comments,
            'register_captcha'    => $this->register_captcha,
            'publish_captcha'     => $this->publish_captcha,
            'always_show_captcha' => $this->always_show_captcha,
        ];

        return $export_settings;
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
        $this->settings->setSetting('user.show_ratings', (int) $this->show_ratings);

        if ($this->favicon_blob_id && $this->favicon_blob_url) {
            $this->settings->setSetting('core.favicon_blob_id', (int) $this->favicon_blob_id);
            $this->settings->setSetting('core.favicon_blob_url', (int) $this->favicon_blob_url);
        } else {
            $this->settings->setSetting('core.favicon_blob_id', null);
            $this->settings->setSetting('core.favicon_blob_url', null);
        }

        foreach ([
            'publish_comments', 'register_captcha', 'publish_captcha',
            'always_show_captcha',
        ] as $p) {
            $this->settings->setSetting("user.$p", (bool) $this->$p);
        }
    }
}
