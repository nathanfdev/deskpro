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

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount;

use Application\DeskPRO\Email\EmailAccount\IncomingAccount\NoopConfig;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\PhpMailConfig;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\ORM\EntityManager;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Orb\Validator\StringEmail;

class EditEmailAccount
{
	/**
	 * @var string
	 */
	public $address;

	/**
	 * @var bool
	 */
	public $is_enabled;

	/**
	 * @var string
	 */
	public $account_type;

	/**
	 * @var string
	 */
	public $other_addresses;

	/**
	 * @var string
	 */
	public $incoming_type;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig
	 */
	public $in_gmail_account;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\Pop3Config
	 */
	public $in_pop3_account;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ImapConfig
	 */
	public $in_imap_account;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ExchangeConfig
	 */
	public $in_exchange_account;

	/**
	 * @var string
	 */
	public $outgoing_type;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\OutgoingAccount\GmailConfig
	 */
	public $out_gmail_account;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\OutgoingAccount\SmtpConfig
	 */
	public $out_smtp_account;

	/**
	 * @var \Application\DeskPRO\Entity\EmailAccount
	 */
	private $account;

	public function __construct(EmailAccount $account)
	{
		$this->account         = $account;
		$this->is_enabled      = $account->is_enabled;
		$this->address         = $account->address;
		$this->account_type    = $account->account_type;
		$this->other_addresses = implode(', ', $account->other_addresses ?: array());
		$this->incoming_type   = $account->incoming_account ? $account->incoming_account->getType() : '';
		$this->outgoing_type   = $account->outgoing_account ? $account->outgoing_account->getType() : '';
	}


	/**
	 * Applies form to the entities.
	 */
	public function apply()
	{
		$this->account->address      = strtolower($this->address);
		$this->account->is_enabled   = $this->is_enabled;
		$this->account->account_type = strtolower($this->account_type);

		if ($this->other_addresses) {
			$emails_arr = array();
			$emails = explode(',', $this->other_addresses);
			foreach ($emails as $email) {
				$email = trim(strtolower($email));
				if (StringEmail::isValueValid($email)) {
					$emails_arr[] = $email;
				}
			}

			$this->account->other_addresses = $emails_arr;
		} else {
			$this->account->other_addresses = null;
		}

		$this->account->incoming_account = $this->getIncomingAccountConfig();
		$this->account->outgoing_account = $this->getOutgoingAccountConfig();
	}

	/**
	 * @param EntityManager $em
	 * @param array         $trigger_actions
	 * @return TicketTrigger
	 */
	public function saveTrigger(EntityManager $em, array $trigger_actions)
	{
		$trigger = $em->createQuery("
			SELECT trigger
			FROM DeskPRO:TicketTrigger trigger
			WHERE trigger.email_account = ?0
		")->setParameters(array($this->account))->getOneOrNullResult();

		if (!$trigger_actions || $this->account->account_type != 'tickets') {
			if ($trigger) {
				$em->remove($trigger);
				$em->flush();
			}
			return null;
		}

		if (!$trigger) {
			$trigger = new TicketTrigger();
			$trigger->email_account = $this->account;
			$trigger->event_trigger = 'newticket';
			$trigger->by_agent_mode = array('email');
			$trigger->by_user_mode  = array('email');
		}

		$actions = new TriggerActions();
		try {
			$actions->importFromArray(array('actions' => $trigger_actions));
		} catch (\Exception $e) {}

		$terms = new TriggerTerms();
		$terms->addTermFromArray(array(
			'type'    => 'CheckEmailAccount',
			'op'      => 'is',
			'options' => array('email_account_ids' => array($this->account->id))
		));

		$trigger->title     = "New Ticket";
		$trigger->run_order = -100;
		$trigger->actions   = $actions;
		$trigger->terms     = $terms;

		$em->persist($trigger);
		$em->flush();

		return $trigger;
	}

	/**
	 * @return \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
	 */
	public function getIncomingAccountConfig()
	{
		switch ($this->incoming_type) {
			case 'pop3':
				return $this->in_pop3_account;

			case 'imap':
				return $this->in_imap_account;

			case 'exchange':
				return $this->in_exchange_account;

			case 'gmail':
				return $this->in_gmail_account;

			case 'noop':
				return new NoopConfig();

			default:
				return null;
		}
	}


	/**
	 * @return \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
	 */
	public function getOutgoingAccountConfig()
	{
		switch ($this->outgoing_type) {
			case 'smtp':
				return $this->out_smtp_account;

			case 'gmail':
				return $this->out_gmail_account;

			case 'php_mail':
				return new PhpMailConfig();

			default;
				return null;
		}
	}
}