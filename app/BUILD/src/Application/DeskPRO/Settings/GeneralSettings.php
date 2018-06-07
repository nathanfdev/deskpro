<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\EntityRepository\BrandSetting;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use Orb\Util\Arrays;

class GeneralSettings
{
    /**
     * @var BrandAwareSettingsResolver
     */
    private $settings;

    /**
     * @var BrandSetting
     */
    private $brandSettingsRepository;

    /**
     * @var string
     */
    public $deskpro_name;

    /**
     * @var bool
     */
    public $deskpro_url_autocorrect;

    /**
     * @var string
     */
    public $deskpro_url;

    /**
     * @var bool
     */
    public $helpdesk_disabled;

    /**
     * @var string
     */
    public $helpdesk_disabled_message;

    /**
     * @var string
     */
    public $site_name;

    /**
     * @var string
     */
    public $site_url;

    /**
     * @var array
     */
    public $default_from_email;

    /**
     * @var string
     */
    public $default_timezone;

    /**
     * @var string
     */
    public $task_reminder_time;

    /**
     * @var string
     */
    public $date_fulltime;

    /**
     * @var string
     */
    public $date_full;

    /**
     * @var string
     */
    public $date_day;

    /**
     * @var string
     */
    public $date_day_short;

    /**
     * @var string
     */
    public $date_time;

    /**
     * @var bool
     */
    public $disable_relative_times = false;

    /**
     * @var array
     */
    public $attach_user_must_exts = [];

    /**
     * @var array
     */
    public $attach_user_not_exts = [];

    /**
     * @var int
     */
    public $attach_user_maxsize;

    /**
     * @var array
     */
    public $attach_agent_must_exts = [];

    /**
     * @var array
     */
    public $attach_agent_not_exts = [];

    /**
     * @var int
     */
    public $attach_agent_maxsize;

    /**
     * @var bool
     */
    protected $rate_limit_disabled;

    /**
     * @var array
     */
    protected $rate_limit_ips;

    /**
     * @var bool
     */
    protected $isCloud;

    /**
     * @param BrandAwareSettingsResolver $settings
     * @param BrandSetting               $brandSettingsRepository
     */
    public function __construct(
        BrandAwareSettingsResolver $settings,
        BrandSetting $brandSettingsRepository,
        Settings $globalSettings
    ) {
        $this->settings                = $settings;
        $this->brandSettingsRepository = $brandSettingsRepository;
        $this->globalSettings          = $globalSettings;
        $this->resetSettings();

        // todo inject. maybe to Settings?
        $this->isCloud = defined('DPC_IS_CLOUD');
    }

    /**
     * Resets settings based on stored values.
     */
    public function resetSettings()
    {
        $this->deskpro_name            = $this->settings->getSetting('core.deskpro_name');
        $this->deskpro_url_autocorrect = (bool) $this->settings->getSetting('core.deskpro_url_autocorrect');
        $this->deskpro_url             = $this->settings->getSetting('core.deskpro_url');

        $this->helpdesk_disabled         = (bool) $this->settings->getSetting('core.helpdesk_disabled');
        $this->helpdesk_disabled_message = $this->settings->getSetting('core.helpdesk_disabled_message');

        $this->site_name = $this->settings->getSetting('core.site_name');
        $this->site_url  = $this->settings->getSetting('core.site_url');

        $this->default_from_email = $this->settings->getAllBrandsSettings('core.default_from_email');
        if (!$this->default_from_email[$this->settings->getSetting('portal.default_brand')]) {
            $this->default_from_email[$this->settings->getSetting('portal.default_brand')] = $this->settings->getSetting('core.default_from_email');
        }

        $this->default_timezone   = $this->settings->getSetting('core.default_timezone');
        $this->task_reminder_time = $this->settings->getSetting('core.task_reminder_time');

        $this->date_fulltime          = $this->settings->getSetting('core.date_fulltime');
        $this->date_full              = $this->settings->getSetting('core.date_full');
        $this->date_day               = $this->settings->getSetting('core.date_day');
        $this->date_day_short         = $this->settings->getSetting('core.date_day_short');
        $this->date_time              = $this->settings->getSetting('core.date_time');
        $this->disable_relative_times = (bool) $this->settings->getSetting('core.disable_relative_times');

        $this->attach_user_maxsize   = $this->settings->getSetting('core.attach_user_maxsize');
        $this->attach_user_must_exts = $this->settings->getSetting('core.attach_user_must_exts');
        if ($this->attach_user_must_exts) {
            $this->attach_user_must_exts = explode(',', $this->attach_user_must_exts);
            $this->attach_user_must_exts = $this->cleanExtsArray($this->attach_user_must_exts);
        } else {
            $this->attach_user_not_exts = $this->settings->getSetting('core.attach_user_not_exts');
            if ($this->attach_user_not_exts) {
                $this->attach_user_not_exts = explode(',', $this->attach_user_not_exts);
                $this->attach_user_not_exts = $this->cleanExtsArray($this->attach_user_not_exts);
            }
        }

        if (!$this->attach_user_must_exts) {
            $this->attach_user_must_exts = [];
        }
        if (!$this->attach_user_not_exts) {
            $this->attach_user_not_exts = [];
        }

        $this->attach_agent_maxsize   = $this->settings->getSetting('core.attach_agent_maxsize');
        $this->attach_agent_must_exts = $this->settings->getSetting('core.attach_agent_must_exts');
        if ($this->attach_agent_must_exts) {
            $this->attach_agent_must_exts = explode(',', $this->attach_agent_must_exts);
            $this->attach_agent_must_exts = $this->cleanExtsArray($this->attach_agent_must_exts);
        } else {
            $this->attach_agent_not_exts = $this->settings->getSetting('core.attach_agent_not_exts');
            if ($this->attach_agent_not_exts) {
                $this->attach_agent_not_exts = explode(',', $this->attach_agent_not_exts);
                $this->attach_agent_not_exts = $this->cleanExtsArray($this->attach_agent_not_exts);
            }
        }

        if (!$this->attach_agent_must_exts) {
            $this->attach_agent_must_exts = [];
        }
        if (!$this->attach_agent_not_exts) {
            $this->attach_agent_not_exts = [];
        }

        $this->rate_limit_disabled = (bool) $this->settings->getSetting(AntiAbuse::SETTING_RATE_LIMIT_IS_DISABLED);
        $this->rate_limit_ips      = json_decode($this->settings->getSetting(AntiAbuse::SETTING_IP_WHITELIST, null, 1) ?: []);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function cleanExtsArray(array $array)
    {
        $array = Arrays::func($array, 'trim');
        $array = Arrays::func($array, 'trim', ['.']);
        $array = Arrays::removeEmptyString($array);
        $array = array_unique($array);
        sort($array, \SORT_STRING);

        return $array;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $export_settings = [
            'deskpro_name'              => $this->deskpro_name,
            'deskpro_url_autocorrect'   => $this->deskpro_url_autocorrect,
            'deskpro_url'               => $this->deskpro_url,
            'helpdesk_disabled'         => $this->helpdesk_disabled,
            'helpdesk_disabled_message' => $this->helpdesk_disabled_message,
            'default_timezone'          => $this->default_timezone,
            'task_reminder_time'        => $this->task_reminder_time,
            'site_name'                 => $this->site_name,
            'site_url'                  => $this->site_url,
            'default_from_email'        => $this->default_from_email,
            'date_fulltime'             => $this->date_fulltime,
            'date_full'                 => $this->date_full,
            'date_day'                  => $this->date_day,
            'date_day_short'            => $this->date_day_short,
            'date_time'                 => $this->date_time,
            'disable_relative_times'    => $this->disable_relative_times,
            'attach_user_must_exts'     => $this->attach_user_must_exts,
            'attach_user_not_exts'      => $this->attach_user_not_exts,
            'attach_user_maxsize'       => $this->attach_user_maxsize,
            'attach_agent_must_exts'    => $this->attach_agent_must_exts,
            'attach_agent_not_exts'     => $this->attach_agent_not_exts,
            'attach_agent_maxsize'      => $this->attach_agent_maxsize,
            'rate_limit_disabled'       => $this->rate_limit_disabled,
            'rate_limit_ips'            => $this->rate_limit_ips,
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
        if ($this->deskpro_url) {
            $this->deskpro_url = App::$container->get('url_host_checker')->simplifyUrl($this->deskpro_url, true, false, true);
            $this->deskpro_url = rtrim($this->deskpro_url, '/').'/';
            $this->globalSettings->setSetting('core.deskpro_url', $this->deskpro_url);
        }

        if (!$this->isCloud) {
            $this->globalSettings->setSetting('core.deskpro_url_autocorrect', (bool) $this->deskpro_url_autocorrect);
            $this->globalSettings->setSetting('core.helpdesk_disabled', (bool) $this->helpdesk_disabled);
            $this->globalSettings->setSetting('core.helpdesk_disabled_message', $this->helpdesk_disabled_message);

            @file_put_contents(App::$container->getParameter('dp.user.cache_dir').'/helpdesk-offline-message.txt', $this->helpdesk_disabled_message);
        }

        $this->site_url = App::$container->get('url_host_checker')->simplifyUrl($this->site_url, true, false, true);

        $this->globalSettings->setSetting('core.deskpro_name', $this->deskpro_name);
        $this->globalSettings->setSetting('core.site_url', $this->site_url);
        $this->globalSettings->setSetting('core.site_name', $this->site_name);
        $this->globalSettings->setSetting('core.default_from_email', $this->default_from_email[$this->settings->getSetting('portal.default_brand')]);

        $brandRepo = App::$container->get('doctrine.orm.default_entity_manager')->getRepository(Brand::class);
        foreach ($this->default_from_email as $brand => $email) {
            $brand = $brandRepo->find($brand);
            if (!$brand) {
                $brandStack = App::$container->get('brand_stack');
                $brand      = $brandStack->getDefaultBrand();
            }
            $this->brandSettingsRepository->updateSetting('core.default_from_email', $email, $brand);
        }

        $this->globalSettings->setSetting('core.default_timezone', $this->default_timezone ?: 'UTC');
        $this->globalSettings->setSetting('core.task_reminder_time', $this->task_reminder_time ?: '09:30');

        $db    = App::$container->get('database_connection');
        $brand = App::$container->getBrandStack()->getDefaultBrand();

        foreach (['core.deskpro_url', 'core.deskpro_name', 'core.site_url', 'core.site_name'] as $copyName) {
            $db->delete('settings_brand', ['name' => $copyName, 'brand_id' => $brand->getId()]);
            $val = $db->fetchColumn('SELECT value FROM settings WHERE name = ?', [$copyName]);
            if ($val) {
                $db->insert('settings_brand', [
                    'brand_id' => $brand->getId(),
                    'name'     => $copyName,
                    'value'    => $val,
                ]);
            }
        }

        foreach (['fulltime', 'full', 'day', 'day_short', 'time'] as $p) {
            $p   = 'date_'.$p;
            $val = trim($this->$p) ?: null;

            $this->globalSettings->setSetting("core.$p", $val);
        }
        $this->globalSettings->setSetting('core.disable_relative_times', $this->disable_relative_times);

        if ($this->attach_user_must_exts) {
            $this->attach_user_must_exts = $this->cleanExtsArray($this->attach_user_must_exts);
            $this->attach_user_not_exts  = [];
        } else {
            $this->attach_user_must_exts = [];
            $this->attach_user_not_exts  = $this->cleanExtsArray($this->attach_user_not_exts);
        }
        $this->globalSettings->setSetting('core.attach_user_maxsize', (int) $this->attach_user_maxsize);
        $this->globalSettings->setSetting('core.attach_user_must_exts', $this->attach_user_must_exts ? implode(',', $this->attach_user_must_exts) : null);
        $this->globalSettings->setSetting('core.attach_user_not_exts', $this->attach_user_not_exts ? implode(',', $this->attach_user_not_exts) : null);

        if ($this->attach_agent_must_exts) {
            $this->attach_agent_must_exts = $this->cleanExtsArray($this->attach_agent_must_exts);
            $this->attach_agent_not_exts  = [];
        } else {
            $this->attach_agent_must_exts = [];
            $this->attach_agent_not_exts  = $this->cleanExtsArray($this->attach_agent_not_exts);
        }
        $this->globalSettings->setSetting('core.attach_agent_maxsize', (int) $this->attach_agent_maxsize);
        $this->globalSettings->setSetting('core.attach_agent_must_exts', $this->attach_agent_must_exts ? implode(',', $this->attach_agent_must_exts) : null);
        $this->globalSettings->setSetting('core.attach_agent_not_exts', $this->attach_agent_not_exts ? implode(',', $this->attach_agent_not_exts) : null);

        $this->globalSettings->setSetting(AntiAbuse::SETTING_RATE_LIMIT_IS_DISABLED, (bool) $this->rate_limit_disabled);
        if (!is_array($this->rate_limit_ips)) {
            $this->rate_limit_ips = [];
        }
        $this->globalSettings->setSetting(AntiAbuse::SETTING_IP_WHITELIST, json_encode($this->rate_limit_ips));
    }
}
