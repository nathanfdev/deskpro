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

namespace Application\DeskPRO\TicketAccounts;

use Application\DeskPRO\Email\EditTransport;
use Application\DeskPRO\Entity\EmailGateway;
use Application\DeskPRO\Entity\EmailGatewayAddress;
use Application\DeskPRO\Entity\EmailTransport;

class EditTicketAccount
{
	/**
	 * @var string
	 */
	public $email_address;

	/**
	 * @var string
	 */
	public $connection_type;

	/**
	 * @var \Application\DeskPRO\Email\IncomingAccount\GmailAccount
	 */
	public $in_gmail_account;

	/**
	 * @var \Application\DeskPRO\Email\IncomingAccount\Pop3Account
	 */
	public $in_pop3_account;

	/**
	 * @var \Application\DeskPRO\Email\IncomingAccount\ImapAccount
	 */
	public $in_imap_account;

	/**
	 * @var \Application\DeskPRO\Email\EditTransport
	 */
	public $email_transport;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGateway
	 */
	private $gateway;

	public function __construct(EmailGateway $gateway)
	{
		$this->gateway = $gateway;

		if ($gateway->linked_transport) {
			$tr = $gateway->linked_transport;
		} else {
			$tr = new EmailTransport();
			$gateway->linked_transport = $tr;
		}

		$this->email_transport = new EditTransport($tr);
	}

	public function apply()
	{
		$this->gateway->email_address = $this->email_address;

		$this->gateway->connection_options = array();
		$this->gateway->connection_type = '';

		if ($this->connection_type == 'pop3') {
			$this->gateway->connection_type = 'pop3';
			$this->gateway->connection_options = $this->in_pop3_account->getOptions();
		} else if ($this->connection_type == 'imap') {
			$this->gateway->connection_type = 'imap';
			$this->gateway->connection_options = $this->in_imap_account->getOptions();
		} else if ($this->connection_type == 'gmail') {
			$this->gateway->connection_type = 'gmail';
			$this->gateway->connection_options = $this->in_gmail_account->getOptions();
		}
	}

	/**
	 * @return \Application\DeskPRO\Email\IncomingAccount\IncomingAccountInterface
	 */
	public function getIncomingAccount()
	{
		if ($this->connection_type == 'pop3') {
			return $this->in_pop3_account;
		} else if ($this->connection_type == 'imap') {
			return $this->in_imap_account;
		} else if ($this->connection_type == 'gmail') {
			return $this->in_gmail_account;
		}
		return null;
	}
}