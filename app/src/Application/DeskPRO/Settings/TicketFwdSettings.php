<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;

class TicketFwdSettings
{
	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\EmailAccountManager
	 */
	private $email_accounts;

	/** @var int */
	public $use_account;
	/** @var bool */
	public $use_agent_address;
	/** @var bool */
	public $process_agent_fwd;
	public $agent_fwd_subject_regex;


	/**
	 * @param Settings            $settings
	 * @param EmailAccountManager $email_accounts
	 */
	public function __construct(Settings $settings, EmailAccountManager $email_accounts)
	{
		$this->settings = $settings;
		$this->email_accounts = $email_accounts;

		$this->resetSettings();
	}


	/**
	 * Resets settings based on stored values.
	 */
	public function resetSettings()
	{
		$this->use_account       = (int)$this->settings->get('core_tickets.fwd_use_account');
		$this->use_agent_address = (bool)$this->settings->get('core_tickets.fwd_use_agent_address');
		$this->process_agent_fwd = (bool)$this->settings->get('core_tickets.process_agent_fwd');
		$this->agent_fwd_subject_regex = $this->settings->get('core_tickets.agent_fwd_subject_regex') ?: '';

		if (!$this->use_account || !$this->email_accounts->hasAcccount($this->use_account)) {
			$this->use_account = null;
		}
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		$export_settings = array();

		foreach (array(
			'use_account',
			'use_agent_address',
			'process_agent_fwd',
			'agent_fwd_subject_regex',
		) as $s) {
			$export_settings[$s] = $this->$s;
		}

		return $export_settings;
	}


	/**
	 * @param array $set_settings
	 */
	public function setArray(array $set_settings)
	{
		$set_settings = new OptionsArray($set_settings);

		$this->use_account       = (int)$set_settings->get('use_account');
		$this->use_agent_address = (bool)$set_settings->get('use_agent_address');
		$this->process_agent_fwd = (bool)$set_settings->get('process_agent_fwd');
		$this->agent_fwd_subject_regex = $set_settings->get('agent_fwd_subject_regex');

		if (!$this->use_account || !$this->email_accounts->hasAcccount($this->use_account)) {
			$this->use_account = null;
		}
	}


	/**
	 * Persists settings
	 */
	public function saveSettings()
	{
		$this->settings->setSetting('core_tickets.fwd_use_account', $this->use_account);
		$this->settings->setSetting('core_tickets.fwd_use_agent_address', $this->use_agent_address);
		$this->settings->setSetting('core_tickets.process_agent_fwd', $this->process_agent_fwd);
		$this->settings->setSetting('core_tickets.agent_fwd_subject_regex', $this->agent_fwd_subject_regex ?: null);
	}
}