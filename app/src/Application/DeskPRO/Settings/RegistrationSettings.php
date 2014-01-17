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

use Doctrine\ORM\EntityManager;

class RegistrationSettings
{
	/**
	 * @var \Application\DeskPRO\Settings\Settings
	 */
	private $settings;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\Entity\Usergroup
	 */
	private $everyone_group;

	public $user_mode;
	public $email_validation;
	public $existing_account_login;

	public $email_validation_trigger_web;
	public $email_validation_trigger_email;
	public $email_validation_trigger_widget;

	public $everyone_group_enabled;


	/**
	 * @param Settings $settings
	 * @param EntityManager $em
	 */
	public function __construct(Settings $settings, EntityManager $em)
	{
		$this->settings = $settings;
		$this->em       = $em;

		$this->everyone_group = $this->em->getRepository('DeskPRO:Usergroup')->findOneBy(array('sys_name' => 'everyone'));

		$this->resetSettings();
	}


	/**
	 * Resets settings based on stored values.
	 */
	public function resetSettings()
	{
		$this->user_mode              = $this->settings->get('core.user_mode');
		$this->email_validation       = (bool)$this->settings->get('core.email_validation');
		$this->existing_account_login = (bool)$this->settings->get('core.existing_account_login');

		$this->email_validation_trigger_web    = false;
		$this->email_validation_trigger_email  = false;
		$this->email_validation_trigger_widget = false;

		$this->everyone_group_enabled = $this->everyone_group->is_enabled;
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		$export_settings = array(
			'user_mode'                       => $this->user_mode,
			'email_validation'                => $this->email_validation,
			'existing_account_login'          => $this->existing_account_login,
			'email_validation_trigger_web'    => $this->email_validation_trigger_web,
			'email_validation_trigger_email'  => $this->email_validation_trigger_email,
			'email_validation_trigger_widget' => $this->email_validation_trigger_widget,
			'everyone_group_enabled'          => $this->everyone_group_enabled,
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
		$this->settings->setSetting("core.user_mode",              $this->user_mode);
		$this->settings->setSetting("core.email_validation",       (int)$this->email_validation);
		$this->settings->setSetting("core.existing_account_login", (int)$this->existing_account_login);

		$this->everyone_group->is_agent_group = (bool)$this->everyone_group_enabled;
		$this->em->persist($this->everyone_group);
		$this->em->flush();
	}
}