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

namespace Application\DeskPRO\Email;

use Application\DeskPRO\Email\OutgoingAccount\GmailAccount;
use Application\DeskPRO\Email\OutgoingAccount\PhpMailAccount;
use Application\DeskPRO\Email\OutgoingAccount\SmtpAccount;
use Application\DeskPRO\Entity\EmailTransport;

class EditTransport
{
	/**
	 * @var string
	 */
	public $email_address;

	/**
	 * @var string
	 */
	public $transport_type;

	/**
	 * @var \Application\DeskPRO\Email\OutgoingAccount\PhpMailAccount
	 */
	public $out_phpmail_account;

	/**
	 * @var \Application\DeskPRO\Email\OutgoingAccount\GmailAccount
	 */
	public $out_gmail_account;

	/**
	 * @var \Application\DeskPRO\Email\OutgoingAccount\SmtpAccount
	 */
	public $out_smtp_account;

	/**
	 * @var \Application\DeskPRO\Entity\EmailTransport
	 */
	private $transport;

	/**
	 * @param EmailTransport $transport
	 */
	public function __construct(EmailTransport $transport)
	{
		$this->transport = $transport;

		$this->email_address = $transport->match_pattern;

		$this->transport_type = $this->transport->transport_type;
		if (!$this->transport_type) {
			$this->transport_type = 'smtp';
		}

		if ($this->transport_type == 'smtp') {
			$this->out_smtp_account = new SmtpAccount();
			$this->out_smtp_account->setOptions($this->transport->transport_options);
		} else if ($this->transport_type == 'gmail') {
			$this->out_gmail_account = new GmailAccount();
			$this->out_gmail_account->setOptions($this->transport->transport_options);
		}

		// Just init the object, its empty
		$this->out_phpmail_account = new PhpMailAccount();
	}

	/**
	 * Applies the current model to the EmailTransport entity
	 */
	public function apply()
	{
		$this->transport->title = $this->email_address;
		$this->transport->transport_type = '';
		$this->transport->transport_options = array();

		$this->transport->match_type = 'exact';
		$this->transport->match_pattern = $this->email_address;

		if ($this->transport_type == 'gmail') {
			$this->transport->transport_type = 'gmail';
			$this->transport->transport_options = $this->out_gmail_account->getOptions();
		} else if ($this->transport_type == 'smtp') {
			$this->transport->transport_type = 'smtp';
			$this->transport->transport_options = $this->out_smtp_account->getOptions();
		} else if ($this->transport_type == 'mail') {
			$this->transport->transport_type = 'mail';
		}
	}


	/**
	 * @return \Application\DeskPRO\Email\OutgoingAccount\OutgoingAccountInterface
	 */
	public function getOutgoingAccount()
	{
		if ($this->transport_type == 'gmail') {
			return $this->out_gmail_account;
		} else if ($this->transport_type == 'smtp') {
			return $this->out_smtp_account;
		} else if ($this->transport_type == 'mail') {
			return $this->out_phpmail_account;
		}

		return null;
	}
}