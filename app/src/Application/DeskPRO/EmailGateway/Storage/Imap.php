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
 * @subpackage EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\Storage;

use Fetch\Server;

class Imap extends Server
{
	/**
	 * @param array $options
	 */
	public function __construct($options = array())
	{
		if (!isset($options['host']) ||
			!isset($options['port']) ||
			!isset($options['username']) ||
			!isset($options['password'])) {
			throw new \Exception('Insufficient Parameters');
		}

		parent::__construct($options['host'], $options['port']);

		if (isset($options['secure'])) {
			switch (strtoupper($options['secure'])) {
				case 'SSL':
					$this->setFlag('ssl');
					break;
				case 'TLS':
					$this->setFlag('tls');
					break;
			}
		}

		$this->setAuthentication($options['user'], $options['password']);
	}


	/**
	 * @return array an array of IDs
	 */
	public function searchUnseen()
	{
		return imap_search($this->getImapStream(),'UNSEEN', SE_UID);
	}


	/**
	 * Searches the server for matching emails and retrieves only the IDs
	 *
	 * @param int $limit
	 * @return array an array of matching IDs
	 */
	public function searchIds($limit)
	{
		$numMessages = $this->numMessages();

		if (isset($limit) && is_numeric($limit) && $limit < $numMessages)
			$numMessages = $limit;

		if ($numMessages < 1)
			return array();

		$stream = $this->getImapStream();

		$messages = array();

		for ($i = 1; $i <= $numMessages; $i++) {
			$messages[] = imap_uid($stream, $i);
		}

		return $messages;
	}


	/**
	 * Gets a raw RFC2822 compatible message
	 *
	 * @param String $messageId Unique message id
	 * @return String Raw message
	 */
	public function getRawMessage($messageId)
	{
		$rawBody = imap_body($this->getImapStream(), $messageId, FT_UID);

		if($rawBody === false){
			throw new \Exception(sprintf('Failed to retrieve raw body for message'));
		}

		return $rawBody;
	}


	/**
	 * Creates a mailbox if it doesnt exist
	 *
	 * @param string $mailbox
	 * @return bool True if it was created, false otherwise
	 */
	public function ensureMailboxExists($mailbox)
	{
		if (!$this->hasMailBox($mailbox)) {
			$this->createMailBox($mailbox);
			return true;
		}

		return false;
	}


	/**
	 * @param \Fetch\Message $message
	 * @return string
	 */
	public function getRawHeaders(\Fetch\Message $message)
	{
		return imap_fetchheader($this->imapStream, $message->getUid(), FT_UID);
	}
}