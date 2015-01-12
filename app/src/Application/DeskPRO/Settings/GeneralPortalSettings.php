<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Settings;

class GeneralPortalSettings
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var array
     */
    private $map = array(
        'site_url'       => array('core.site_url', 'string'),
        'site_name'      => array('core.site_name', 'string'),
        'deskpro_url'    => array('core.deskpro_url', 'string'),
        'deskpro_name'   => array('core.deskpro_name', 'string'),

        'apps_feedback'  => array('core.apps_feedback', 'bool'),
        'apps_kb'        => array('core.apps_kb', 'bool'),
        'apps_news'      => array('core.apps_news', 'bool'),
        'apps_downloads' => array('core.apps_downloads', 'bool'),

        'iface_portal'   => array('core.iface_portal', 'bool'),
        'iface_widget'   => array('core.iface_widget', 'bool'),
    );

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
    public $iface_portal;

    /**
     * @var bool
     */
    public $iface_widget;

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
                    $this->$name = (bool)$v;
                    break;
                default:
                    $this->$name = $v ? (($v . '') ?: '') : '';
            }
        }
    }


    /**
     * @return array
     */
    public function toArray()
    {
        $export_settings = array();

        foreach ($this->map as $name => $info) {
            switch ($info[1]) {
                case 'bool':
                    $export_settings[$name] = (bool)$this->$name;
                    break;
                default:
                    $export_settings[$name] = $this->$name ? (($this->$name . '') ?: '') : '';
            }
        }

        if ($this->apps_downloads || $this->apps_feedback || $this->apps_kb || $this->apps_news) {
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
                foreach (array('apps_feedback', 'apps_kb', 'apps_news', 'apps_downloads') as $n) {
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
                    $this->$name = (bool)$v;
                    break;
                default:
                    $this->$name = $v ? (($v . '') ?: '') : '';
            }
        }
    }


    /**
     * Persists settings
     */
    public function saveSettings()
    {
        foreach ($this->map as $name => $info) {
            $this->settings->setSetting($info[0], $this->$name);
        }
    }
}
