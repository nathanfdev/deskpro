<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings;

/**
 * Class GeneralPortalSettings.
 *
 * @deprecated Please use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\GeneralSettings instead
 */
class GeneralPortalSettings
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var array
     */
    private $map = [
        'site_url'     => ['core.site_url', 'string'],
        'site_name'    => ['core.site_name', 'string'],
        'deskpro_url'  => ['core.deskpro_url', 'string'],
        'deskpro_name' => ['core.deskpro_name', 'string'],

        'apps_feedback'  => ['core.apps_feedback', 'bool'],
        'apps_kb'        => ['core.apps_kb', 'bool'],
        'apps_news'      => ['core.apps_news', 'bool'],
        'apps_downloads' => ['core.apps_downloads', 'bool'],

        'iface_portal' => ['core.iface_portal', 'bool'],
        'iface_widget' => ['core.iface_widget', 'bool'],

        'show_ratings'           => ['user.show_ratings', 'bool'],
        'show_ratings_min_votes' => ['user.show_ratings_min_votes', 'int'],
        'publish_comments'       => ['user.publish_comments', 'bool'],
    ];

    /**
     * @var string
     */
    public $site_url;

    /**
     * @var string
     */
    public $site_name;

    /**
     * @var string
     */
    public $deskpro_name;

    /**
     * @var string
     */
    public $deskpro_url;

    /**
     * @var bool
     */
    public $apps_feedback;

    /**
     * @var bool
     */
    public $apps_kb;

    /**
     * @var bool
     */
    public $apps_news;

    /**
     * @var bool
     */
    public $apps_downloads;

    /**
     * @var bool
     */
    public $apps_guides;

    /**
     * @var bool
     */
    public $iface_portal;

    /**
     * @var bool
     */
    public $iface_widget;

    /**
     * @var bool
     */
    public $show_ratings;

    /**
     * @var int
     */
    public $show_ratings_min_votes;

    /**
     * @var bool
     */
    public $publish_comments;

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
        foreach ($this->map as $name => $info) {
            $v = $this->settings->get($info[0]);
            switch ($info[1]) {
                case 'bool':
                    $this->$name = (bool) $v;
                    break;
                case 'int':
                    $this->$name = (int) $v;
                    break;
                default:
                    $this->$name = $v ? (($v.'') ?: '') : '';
            }
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $export_settings = [];

        foreach ($this->map as $name => $info) {
            switch ($info[1]) {
                case 'bool':
                    $export_settings[$name] = (bool) $this->$name;
                    break;
                case 'int':
                    $export_settings[$name] = (int) $this->$name;
                    break;
                default:
                    $export_settings[$name] = $this->$name ? (($this->$name.'') ?: '') : '';
            }
        }

        if ($this->apps_downloads || $this->apps_feedback || $this->apps_kb || $this->apps_news || $this->apps_guides) {
            $export_settings['portal_mode'] = 'publish';
        } else {
            $export_settings['portal_mode'] = 'tickets';
        }

        return $export_settings;
    }

    /**
     * @param array $set_settings
     */
    public function setArray(array $set_settings)
    {
        if (isset($set_settings['portal_mode'])) {
            if ($set_settings['portal_mode'] != 'publish') {
                foreach (['apps_feedback', 'apps_kb', 'apps_news', 'apps_downloads'] as $n) {
                    $set_settings[$n] = 0;
                }
            }
        }

        foreach ($this->map as $name => $info) {
            if (!isset($set_settings[$name])) {
                continue;
            }
            $v = $set_settings[$name];

            switch ($info[1]) {
                case 'bool':
                    $this->$name = (bool) $v;
                    break;
                case 'int':
                    $this->$name = (int) $v;
                    break;
                default:
                    $this->$name = $v ? (($v.'') ?: '') : '';
            }
        }
    }

    /**
     * Persists settings.
     */
    public function saveSettings()
    {
        foreach ($this->map as $name => $info) {
            $this->settings->setSetting($info[0], $this->$name);
        }

        $this->settings->setSetting('user.portal_enabled', $this->iface_portal);
    }
}
