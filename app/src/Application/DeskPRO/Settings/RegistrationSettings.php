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

use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Actions\SetRequireValidation;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUserIsNew;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use Doctrine\ORM\EntityManager;

class RegistrationSettings
{
	/**
	 * @var \Application\DeskPRO\Settings\Settings
	 */
	private $settings;

	/**
	 * @var \Application\DeskPRO\Entity\TicketTrigger
	 */
	private $email_validation_trigger;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\Entity\Usergroup
	 */
	private $everyone_group;

	public $reg_enabled;
	public $reg_required;
	public $email_validation;
	public $agent_validation;
	public $existing_account_login;
	public $everyone_group_enabled;
	public $email_validation_ticket_web;
	public $email_validation_ticket_email;


	/**
	 * @param Settings $settings
	 * @param EntityManager $em
	 */
	public function __construct(Settings $settings, EntityManager $em)
	{
		$this->settings = $settings;
		$this->em       = $em;

		$this->everyone_group = $this->em->getRepository('DeskPRO:Usergroup')->findOneBy(array('sys_name' => 'everyone'));

		$this->email_validation_trigger = $this->em->createQuery("
			SELECT t
			FROM DeskPRO:TicketTrigger t
			WHERE t.sys_name = 'default_newticket_requirevalid'
		")->setMaxResults(1)->getOneOrNullResult();

		$this->resetSettings();
	}


	/**
	 * Resets settings based on stored values.
	 */
	public function resetSettings()
	{
		$this->reg_enabled            = (bool)$this->settings->get('core.reg_enabled');
		$this->reg_required           = (bool)$this->settings->get('core.reg_required');
		$this->email_validation       = (bool)$this->settings->get('core.email_validation');
		$this->agent_validation       = (bool)$this->settings->get('core.agent_validation');
		$this->existing_account_login = (bool)$this->settings->get('core.existing_account_login');
		$this->everyone_group_enabled = (bool)$this->everyone_group->is_enabled;

		if ($this->email_validation_trigger && $this->email_validation_trigger->is_enabled) {
			if (in_array('email', $this->email_validation_trigger->by_user_mode)) {
				$this->email_validation_ticket_email = true;
			}
			if (in_array('portal', $this->email_validation_trigger->by_user_mode)) {
				$this->email_validation_ticket_web = true;
			}
		}
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		$export_settings = array(
			'reg_enabled'            => $this->reg_enabled,
			'reg_required'           => $this->reg_required,
			'email_validation'       => $this->email_validation,
			'agent_validation'       => $this->agent_validation,
			'existing_account_login' => $this->existing_account_login,
			'everyone_group_enabled' => $this->everyone_group_enabled,

			'email_validation_ticket_web'    => $this->email_validation_ticket_web,
			'email_validation_ticket_email'  => $this->email_validation_ticket_email,
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
		if ($this->reg_enabled) {
			$this->settings->setSetting('core.reg_enabled', 1);
			$this->settings->setSetting('core.reg_required', (int)$this->reg_required);
			$this->settings->setSetting('core.email_validation', (int)$this->email_validation);
			$this->settings->setSetting('core.agent_validation', (int)$this->agent_validation);
		} else {
			$this->settings->setSetting('core.reg_enabled', 0);
			$this->settings->setSetting('core.reg_required', 0);
			$this->settings->setSetting('core.email_validation', 0);
			$this->settings->setSetting('core.agent_validation', 0);
		}

		$this->settings->setSetting("core.existing_account_login", (int)$this->existing_account_login);

		if (!$this->email_validation_trigger) {

		}

		if (!$this->email_validation_ticket_email && !$this->email_validation_ticket_web) {
			if ($this->email_validation_trigger) {
				$this->email_validation_trigger->by_user_mode = array('email', 'form', 'portal', 'widget');
				$this->email_validation_trigger->is_enabled   = false;
			}
		} else {
			if (!$this->email_validation_trigger) {
				$this->email_validation_trigger = $this->_createTrigger();
			}

			$this->email_validation_trigger->is_enabled = true;
			$mode = array();
			if ($this->email_validation_ticket_web) {
				$mode[] = 'form';
				$mode[] = 'portal';
				$mode[] = 'widget';
			}
			if ($this->email_validation_ticket_email) {
				$mode[] = 'email';
			}

			$this->email_validation_trigger->by_user_mode = $mode;
		}

		$this->everyone_group->is_enabled = (bool)$this->everyone_group_enabled;
		$this->em->persist($this->everyone_group);

		if ($this->email_validation_trigger) {
			$this->em->persist($this->email_validation_trigger);
		}

		$this->em->flush();
	}


	/**
	 * @return TicketTrigger
	 * @throws \InvalidArgumentException
	 */
	private function _createTrigger()
	{
		$trigger = new TicketTrigger();
		$trigger->event_trigger = 'newticket';
		$trigger->run_order     = -1000;
		$trigger->by_user_mode  = array('email', 'form', 'portal', 'widget');
		$trigger->is_enabled    = true;
		$trigger->is_hidden     = true;
		$trigger->sys_name      = 'default_newticket_requirevalid';
		$trigger->title         = 'Enable email validation';

		$set = new TriggerTermComposite();
		$set->setOperator('AND');
		$set->add(new CheckUserIsNew('is'));
		$trigger->terms->addTerm($set);

		$trigger->actions->addAction(new SetRequireValidation(array('require_validation' => true)));

		return $trigger;
	}
}