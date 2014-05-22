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

namespace Application\DeskPRO\Tickets\Triggers\Edit;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckDepartment;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckEmailAccount;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;

class SpecialTriggerEdit
{
	const TYPE_DEPARTMENT = 'Department';
	const TYPE_EMAIL_ACCOUNT = 'EmailAccount';

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var \Application\DeskPRO\Entity\Department|\Application\DeskPRO\Entity\EmailAccount
	 */
	private $obj;


	/**
	 * @param Department $department
	 * @return SpecialTriggerEdit
	 */
	public static function createWithDepartment(Department $department)
	{
		return new self(self::TYPE_DEPARTMENT, $department);
	}


	/**
	 * @param EmailAccount $account
	 * @return SpecialTriggerEdit
	 */
	public static function createWithEmailAccount(EmailAccount $account)
	{
		return new self(self::TYPE_EMAIL_ACCOUNT, $account);
	}


	/**
	 * @param string $type
	 * @param Department|EmailAccount $obj
	 */
	private function __construct($type, $obj)
	{
		$this->type = $type;
		$this->obj  = $obj;
	}


	/**
	 * @param TicketTrigger $trigger
	 */
	public function applyToTrigger(TicketTrigger $trigger)
	{
		switch ($this->type) {
			case self::TYPE_DEPARTMENT:
				$this->applyDepartmentToTrigger($trigger);
				break;

			case self::TYPE_EMAIL_ACCOUNT:
				$this->applyEmailAccountToTrigger($trigger);
				break;
		}
	}


	/**
	 * @param TicketTrigger $trigger
	 */
	private function applyDepartmentToTrigger(TicketTrigger $trigger)
	{
		$trigger->title         = "Trigger for Department: {$this->obj->title}";
		$trigger->department    = $this->obj;
		$trigger->email_account = null;

		$terms = new TriggerTerms();
		$terms_set = new TriggerTermComposite();
		$terms_set->add(new CheckDepartment('is', array('department_ids' => array($this->obj->id))));
		$terms->addTerm($terms_set);

		$trigger->terms = $terms;

		$trigger->event_trigger = 'newticket';
		$trigger->by_agent_mode = array();
		$trigger->by_user_mode  = array('form', 'portal', 'widget');
	}


	/**
	 * @param TicketTrigger $trigger
	 */
	private function applyEmailAccountToTrigger(TicketTrigger $trigger)
	{
		$trigger->title         = "Trigger for Email Account: {$this->obj->address}";
		$trigger->email_account = $this->obj;
		$trigger->department    = null;

		$terms = new TriggerTerms();
		$terms_set = new TriggerTermComposite();
		$terms_set->add(new CheckEmailAccount('is', array('email_account_ids' => array($this->obj->id))));
		$terms->addTerm($terms_set);

		$trigger->terms = $terms;

		$trigger->event_trigger = 'newticket';
		$trigger->by_agent_mode = array();
		$trigger->by_user_mode  = array('email');
	}
}