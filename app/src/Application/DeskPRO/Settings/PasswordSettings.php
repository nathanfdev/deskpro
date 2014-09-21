<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

use Orb\Util\Numbers;

class PasswordSettings
{
	/**
	 * @var \Application\DeskPRO\Settings\Settings
	 */
	private $settings;

	/**
	 * @var PasswordPolicy
	 */
	private $user_policy;

	/**
	 * @var PasswordPolicy
	 */
	private $agent_policy;

	/** @var int */
	public $sessions_lifetime = 3600;
	/** @var bool */
	public $session_keepalive_require_page = false;
	/** @var bool */
	public $ip_security_enabled = false;
	/** @var string */
	public $ip_security_mode = 'admins';
	/** @var int */
	public $ip_security_whitelist_lifetime = 1814400;
	/** @var bool */
	public $disable_notifications;
	/** @var bool */
	public $enable_agent_rememberme;
	/** @var bool */
	public $enable_user_rememberme;


	/**
	 * @param Settings $settings
	 */
	public function __construct(Settings $settings)
	{
		$this->settings = $settings;

		$this->user_policy  = new PasswordPolicy();
		$this->agent_policy = new PasswordPolicy();

		$this->resetSettings();
	}


	/**
	 * @return PasswordPolicy
	 */
	public function getUserPolicy()
	{
		return $this->user_policy;
	}


	/**
	 * @return PasswordPolicy
	 */
	public function getAgentPolicy()
	{
		return $this->agent_policy;
	}


	/**
	 * Resets settings based on stored values.
	 */
	public function resetSettings()
	{
		foreach (array('user', 'agent') as $type) {
			$type_obj = $this->{$type . "_policy"};

			foreach (array(
				'min_length',
				'max_age',
				'forbid_reuse',
				'require_num_uppercase',
				'require_num_lowercase',
				'require_num_number',
				'require_num_symbol',
			) as $name) {
				$type_obj->$name = $this->settings->get("$type.password_policy.$name");
			}

			$type_obj->verify();
		}

		$this->sessions_lifetime              = (int)$this->settings->get('core.sessions_lifetime');
		$this->session_keepalive_require_page = (bool)$this->settings->get('core.session_keepalive_require_page');
		$this->ip_security_enabled            = (bool)$this->settings->get('agent.ip_security.enabled');
		$this->ip_security_mode               = $this->settings->get('agent.ip_security.mode');
		$this->ip_security_whitelist_lifetime = (int)$this->settings->get('agent.ip_security.whitelist_lifetime');
		$this->disable_notifications          = (bool)$this->settings->get('agent.disable_notifications');
		$this->enable_agent_rememberme        = (bool)$this->settings->get('core.enable_agent_rememberme');
		$this->enable_user_rememberme         = (bool)$this->settings->get('core.enable_user_rememberme');
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'user'  => $this->user_policy->toArray(),
			'agent' => $this->agent_policy->toArray(),
			'sessions_lifetime'               => $this->sessions_lifetime,
			'session_keepalive_require_page'  => $this->session_keepalive_require_page,
			'ip_security_enabled'             => $this->ip_security_enabled,
			'ip_security_mode'                => $this->ip_security_mode,
			'ip_security_whitelist_lifetime'  => $this->ip_security_whitelist_lifetime,
			'disable_notifications'           => $this->disable_notifications,
			'enable_agent_rememberme'         => $this->enable_agent_rememberme,
			'enable_user_rememberme'          => $this->enable_user_rememberme,
		);
	}


	/**
	 * @param array $set_settings
	 */
	public function setArray(array $set_settings)
	{
		$this->user_policy->fromArray($set_settings['user']);
		$this->agent_policy->fromArray($set_settings['agent']);
		$this->sessions_lifetime = $set_settings['sessions_lifetime'];
		$this->session_keepalive_require_page = $set_settings['session_keepalive_require_page'];

		$this->ip_security_enabled            = !empty($set_settings['ip_security_enabled']) && $set_settings['ip_security_enabled'];
		$this->ip_security_mode               = $set_settings['ip_security_mode'] ?: 'admins';
		$this->ip_security_whitelist_lifetime = ((int)$set_settings['ip_security_whitelist_lifetime']) ?: 1814400;
		$this->disable_notifications          = (bool)$set_settings['disable_notifications'];
		$this->enable_agent_rememberme        = (bool)$set_settings['enable_agent_rememberme'];
		$this->enable_user_rememberme         = (bool)$set_settings['enable_user_rememberme'];
	}


	/**
	 * Persists settings
	 */
	public function saveSettings()
	{
		foreach (array('user', 'agent') as $type) {
			$type_obj = $this->{$type . "_policy"};

			foreach ($type_obj->toArray() as $k => $v) {
				if (is_bool($v)) $v = $v ? 1 : 0;
				$this->settings->setSetting("$type.password_policy.$k", $v);
			}
		}

		$this->settings->setSetting('core.sessions_lifetime', Numbers::bound($this->sessions_lifetime, 300, 86400));

		if ($this->session_keepalive_require_page) {
			$this->settings->setSetting('core.session_keepalive_require_page', 1);
		} else {
			$this->settings->setSetting('core.session_keepalive_require_page', null);
		}

		$this->settings->setSetting('agent.ip_security.enabled',            $this->ip_security_enabled ? 1 : 0);
		$this->settings->setSetting('agent.ip_security.mode',               $this->ip_security_mode);
		$this->settings->setSetting('agent.ip_security.whitelist_lifetime', $this->ip_security_whitelist_lifetime);
		$this->settings->setSetting('agent.disable_notifications',          (bool)$this->disable_notifications);
		$this->settings->setSetting('core.enable_agent_rememberme',         (bool)$this->enable_agent_rememberme);
		$this->settings->setSetting('core.enable_user_rememberme',          (bool)$this->enable_user_rememberme);
	}
}