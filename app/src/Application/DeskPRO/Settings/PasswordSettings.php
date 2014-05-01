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
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'user'  => $this->user_policy->toArray(),
			'agent' => $this->agent_policy->toArray()
		);
	}


	/**
	 * @param array $set_settings
	 */
	public function setArray(array $set_settings)
	{
		$this->user_policy->fromArray($set_settings['user']);
		$this->agent_policy->fromArray($set_settings['agent']);
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
	}
}