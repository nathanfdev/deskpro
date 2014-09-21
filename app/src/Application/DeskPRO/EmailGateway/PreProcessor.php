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

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\EmailSource;

class PreProcessor extends AbstractGatewayProcessor
{
	/** @var string|null */
	protected $error = null;
	/** @var string */
	protected $error_type = 'rejected';
	/** @var array|null */
	protected $source_info = null;

	public function run()
	{
		#------------------------------
		# Empty From
		#------------------------------

		$from = $this->reader->getFromAddress()->getEmail();
		if (!$from) {
			$this->error = EmailSource::ERR_FROM_MISSING;
			return;
		}

		#------------------------------
		# Invalid From
		#------------------------------

		$validator = new \Orb\Validator\StringEmail();

		if (!$validator->isValid($from)) {
			$this->error = EmailSource::ERR_FROM_INVALID;
			$this->source_info = array();
			$this->source_info[] = "Read from address: " . $from;
			$this->source_info[] = "Errors:\n\n" . $validator->getErrorsDebug();
			return;
		}

		#------------------------------
		# From is a know gateway address
		#------------------------------

		$account_manager = App::$container->getEmailAccountManager();
		if ($found_account = $account_manager->findAccountForEmailAddress($from)) {
			$this->error = EmailSource::ERR_FROM_GATEWAY;
			$this->source_info[] = "Read from address: " . $from;
			$this->source_info[] = "Matched account: " . $found_account->id;
			$this->source_info[] = "Account addresses: " . implode(', ', $found_account->getAllAddresses());
			return;
		}

		#------------------------------
		# From is a banned address
		#------------------------------

		$match = null;
		if (App::getOrm()->getRepository('DeskPRO:BanEmail')->isEmailBanned($from, $match)) {
			$this->error = EmailSource::ERR_FROM_BANNED;
			$this->source_info[] = "Read from address: " . $from;
			$this->source_info[] = "Matched banned email: " . $match;
			return;
		}

		#------------------------------
		# Check for empty message
		#------------------------------

		$subj = trim($this->reader->getSubject()->getSubject());
		$message = trim($this->reader->getBodyHtml()->getBody());
		$message2 = trim($this->reader->getBodyText()->getBody());
		$attach = $this->reader->getAttachments();

		if (!$subj && !$message && !$message2 && !$attach) {
			$this->error = EmailSource::ERR_EMPTY;
			return;
		}

		#------------------------------
		# Check if date is older than start_date_limit
		# on the account
		#------------------------------

		if ($this->account->date_read_start && $email_date = $this->reader->getDate() && App::getSetting('core_email.enable_date_limit_rejection')) {
			if ($email_date < $this->account->date_read_start) {
				$this->error = EmailSource::ERR_DATE_LIMIT;
				$this->source_info[] = "Gateway date limit: " . $this->account->date_read_start->format(\DateTime::RFC2822);
				$this->source_info[] = "Message date: " . $email_date->format(\DateTime::RFC2822);
				return;
			}
		}

		unset($subj, $message, $message2, $attach);
	}

	public function isValid()
	{
		return $this->error === null;
	}

	/**
	 * 'error' or 'rejected'
	 * @return string
	 */
	public function getErrorType()
	{
		return $this->error_type;
	}

	public function getErrorCode()
	{
		return $this->error;
	}

	public function getSourceInfo()
	{
		if (!$this->source_info) {
			return null;
		}

		if (!is_array($this->source_info)) {
			$this->source_info = array($this->source_info);
		}
		return $this->source_info;
	}
}