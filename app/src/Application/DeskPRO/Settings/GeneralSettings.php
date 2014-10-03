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
use Orb\Util\Arrays;

class GeneralSettings
{
	/**
	 * @var Settings
	 */
	private $settings;

	public $deskpro_name;
	public $deskpro_url;
	public $helpdesk_disabled;
	public $helpdesk_disabled_message;
	public $site_name;
	public $site_url;

	public $default_from_email;

	public $default_timezone;
	public $task_reminder_time;

	public $date_fulltime;
	public $date_full;
	public $date_day;
	public $date_day_short;
	public $date_time;

	public $attach_user_must_exts = array();
	public $attach_user_not_exts = array();
	public $attach_user_maxsize;

	public $attach_agent_must_exts = array();
	public $attach_agent_not_exts = array();
	public $attach_agent_maxsize;

	protected $isCloud;

	/**
	 * @param Settings $settings
	 */
	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
		$this->resetSettings();

		// todo inject. maybe to Settings?
		$this->isCloud = defined('DPC_IS_CLOUD');
	}


	/**
	 * Resets settings based on stored values.
	 */
	public function resetSettings()
	{
		$this->deskpro_name = $this->settings->get('core.deskpro_name');
		$this->deskpro_url  = $this->settings->get('core.deskpro_url');

		$this->helpdesk_disabled = (bool) $this->settings->get('core.helpdesk_disabled');
		$this->helpdesk_disabled_message = $this->settings->get('core.helpdesk_disabled_message');

		$this->site_name    = $this->settings->get('core.site_name');
		$this->site_url     = $this->settings->get('core.site_url');

		$this->default_from_email = $this->settings->get('core.default_from_email');

		$this->default_timezone   = $this->settings->get('core.default_timezone');
		$this->task_reminder_time = $this->settings->get('core.task_reminder_time');

		$this->date_fulltime  = $this->settings->get('core.date_fulltime');
		$this->date_full      = $this->settings->get('core.date_full');
		$this->date_day       = $this->settings->get('core.date_day');
		$this->date_day_short = $this->settings->get('core.date_day_short');
		$this->date_time      = $this->settings->get('core.date_time');

		$this->attach_user_maxsize = $this->settings->get('core.attach_user_maxsize');
		$this->attach_user_must_exts = $this->settings->get('core.attach_user_must_exts');
		if ($this->attach_user_must_exts) {
			$this->attach_user_must_exts = explode(',', $this->attach_user_must_exts);
			$this->attach_user_must_exts = $this->cleanExtsArray($this->attach_user_must_exts);
		} else {
			$this->attach_user_not_exts = $this->settings->get('core.attach_user_not_exts');
			if ($this->attach_user_not_exts) {
				$this->attach_user_not_exts = explode(',', $this->attach_user_not_exts);
				$this->attach_user_not_exts = $this->cleanExtsArray($this->attach_user_not_exts);
			}
		}

		if (!$this->attach_user_must_exts) $this->attach_user_must_exts = array();
		if (!$this->attach_user_not_exts)  $this->attach_user_not_exts = array();

		$this->attach_agent_maxsize = $this->settings->get('core.attach_agent_maxsize');
		$this->attach_agent_must_exts = $this->settings->get('core.attach_agent_must_exts');
		if ($this->attach_agent_must_exts) {
			$this->attach_agent_must_exts = explode(',', $this->attach_agent_must_exts);
			$this->attach_agent_must_exts = $this->cleanExtsArray($this->attach_agent_must_exts);
		} else {
			$this->attach_agent_not_exts = $this->settings->get('core.attach_agent_not_exts');
			if ($this->attach_agent_not_exts) {
				$this->attach_agent_not_exts = explode(',', $this->attach_agent_not_exts);
				$this->attach_agent_not_exts = $this->cleanExtsArray($this->attach_agent_not_exts);
			}
		}

		if (!$this->attach_agent_must_exts) $this->attach_agent_must_exts = array();
		if (!$this->attach_agent_not_exts)  $this->attach_agent_not_exts = array();
	}


	/**
	 * @param array $array
	 * @return array
	 */
	private function cleanExtsArray(array $array)
	{
		$array = Arrays::func($array, 'trim');
		$array = Arrays::func($array, 'trim', array('.'));
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
		$export_settings = array(
			'deskpro_name'           => $this->deskpro_name,
			'deskpro_url'            => $this->deskpro_url,
			'helpdesk_disabled'      => $this->helpdesk_disabled,
			'helpdesk_disabled_message' => $this->helpdesk_disabled_message,
			'default_timezone'       => $this->default_timezone,
			'task_reminder_time'     => $this->task_reminder_time,
			'site_name'              => $this->site_name,
			'site_url'               => $this->site_url,
			'default_from_email'     => $this->default_from_email,
			'date_fulltime'          => $this->date_fulltime,
			'date_full'              => $this->date_full,
			'date_day'               => $this->date_day,
			'date_day_short'         => $this->date_day_short,
			'date_time'              => $this->date_time,
			'attach_user_must_exts'  => $this->attach_user_must_exts,
			'attach_user_not_exts'   => $this->attach_user_not_exts,
			'attach_user_maxsize'    => $this->attach_user_maxsize,
			'attach_agent_must_exts' => $this->attach_agent_must_exts,
			'attach_agent_not_exts'  => $this->attach_agent_not_exts,
			'attach_agent_maxsize'   => $this->attach_agent_maxsize,

		);
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
	 * Persists settings
	 */
	public function saveSettings()
	{
		if ($this->deskpro_url) {
			$this->deskpro_url = rtrim($this->deskpro_url, '/') . '/';
			$this->settings->setSetting('core.deskpro_url', $this->deskpro_url);
		}

		if (!$this->isCloud) {
			$this->settings->setSetting('core.helpdesk_disabled', (bool) $this->helpdesk_disabled);
			$this->settings->setSetting('core.helpdesk_disabled_message', $this->helpdesk_disabled_message);

			// todo? legacy code
			@file_put_contents(dp_get_data_dir() . '/helpdesk-offline-message.txt', $this->helpdesk_disabled_message);
		}

		$this->settings->setSetting('core.deskpro_name', $this->deskpro_name);
		$this->settings->setSetting('core.site_url',  $this->site_url);
		$this->settings->setSetting('core.site_name', $this->site_name);
		$this->settings->setSetting('core.default_from_email', $this->default_from_email);

		$this->settings->setSetting('core.default_timezone',  $this->default_timezone ?: 'UTC');
		$this->settings->setSetting('core.task_reminder_time', $this->task_reminder_time ?: '09:30');

		foreach (array('fulltime', 'full', 'day', 'day_short', 'time') as $p) {
			$p = 'date_' . $p;
			$val = trim($this->$p) ?: null;

			$this->settings->setSetting("core.$p", $val);
		}

		if ($this->attach_user_must_exts) {
			$this->attach_user_must_exts = $this->cleanExtsArray($this->attach_user_must_exts);
			$this->attach_user_not_exts = array();
		} else {
			$this->attach_user_must_exts = array();
			$this->attach_user_not_exts = $this->cleanExtsArray($this->attach_user_not_exts);
		}
		$this->settings->setSetting('core.attach_user_maxsize', (int)$this->attach_user_maxsize);
		$this->settings->setSetting('core.attach_user_must_exts', $this->attach_user_must_exts ? implode(',', $this->attach_user_must_exts) : null);
		$this->settings->setSetting('core.attach_user_not_exts', $this->attach_user_not_exts ? implode(',', $this->attach_user_not_exts) : null);

		if ($this->attach_agent_must_exts) {
			$this->attach_agent_must_exts = $this->cleanExtsArray($this->attach_agent_must_exts);
			$this->attach_agent_not_exts = array();
		} else {
			$this->attach_agent_must_exts = array();
			$this->attach_agent_not_exts = $this->cleanExtsArray($this->attach_agent_not_exts);
		}
		$this->settings->setSetting('core.attach_agent_maxsize', (int)$this->attach_agent_maxsize);
		$this->settings->setSetting('core.attach_agent_must_exts', $this->attach_agent_must_exts ? implode(',', $this->attach_agent_must_exts) : null);
		$this->settings->setSetting('core.attach_agent_not_exts', $this->attach_agent_not_exts ? implode(',', $this->attach_agent_not_exts) : null);
	}
}