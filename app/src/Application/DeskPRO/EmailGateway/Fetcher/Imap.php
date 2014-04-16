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

namespace Application\DeskPRO\EmailGateway\Fetcher;

use Application\DeskPRO\EmailGateway\Storage;

/**
 * Fetches mail from a imap server
 */
class Imap extends AbstractFetcher
{
	/**
	 * Just marks messages as read once they are processed.
	 */
	const MODE_READ = 'read';

	/**
	 * Deletes messages once they are processed.
	 */
	const MODE_DELETE = 'delete';

	/**
	 * Archive messages (moves to a folder) once they are processed.
	 */
	const MODE_ARCHIVE = 'archive';

	/**
	 * @var int
	 */
	private $mode = self::MODE_READ;

	/**
	 * The IMAP Storage
	 *
	 * @var \Application\DeskPRO\EmailGateway\Storage\Imap
	 */
	private $storage;

	/**
	 * Messages retrieved in the current fetch
	 *
	 * @var Array An array of \Fetch\Message
	 */
	private $messages;

	/**
	 * Mailbox name to move messages after processing
	 * @var String Mailbox name
	 */
	private $archive_mailbox;

	/**
	 * Max number of email IDs to fetch in one go
	 * @var int
	 */
	private $fetch_limit = 10;

	/**
	 * Next Message index to read
	 *
	 * @var int
	 */
	private $next_id = 0;


	/**
	 * Initiates the connection
	 *
	 * @return \Zend\Mail\Storage\Pop3
	 */
	protected function _initConnection()
	{
		$options = array();

		switch ($this->account->incoming_account->getType()) {
			case 'imap':
				/** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ImapConfig $imap_config */
				$imap_config = $this->account->incoming_account;

				$options['host']     = $imap_config->host;
				$options['port']     = $imap_config->port;
				$options['user']     = $imap_config->user;
				$options['password'] = $imap_config->password;
				$options['mode']     = $imap_config->mode;

				if ($imap_config->secure_mode) {
					$options['secure'] = $imap_config->secure_mode;
				}

				if ($imap_config->mode == self::MODE_ARCHIVE) {
					$options['archive_mailbox'] = $imap_config->archive_mailbox;
				}

				break;

			case 'gmail':
				/** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig $gmail_config */
				$gmail_config = $this->account->incoming_account;

				$options['host']     = 'imap.gmail.com';
				$options['port']     = 993;
				$options['user']     = $gmail_config->user;
				$options['password'] = $gmail_config->password;
				$options['mode']     = self::MODE_DELETE; // delete in gmail just means archive
				$options['secure']   = 'ssl';

			default:
				throw new \InvalidArgumentException("Unknown account type: " . $this->account->incoming_account->getType());
		}

		$this->mode = $options['mode'];
		$this->archive_mailbox = !empty($options['archive_mailbox']) ? $options['archive_mailbox'] : 'DP_Archive';

		$this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');

		$options['logger'] = $this->logger;

		$this->storage = new Storage\Imap($options);

		if ($this->archive_mailbox === $this->storage->getMailbox()) {
			throw new \Exception("The current mailbox is reserved for processed emails, it can not be used as the primary mailbox");
		}

		if ($this->mode === self::MODE_ARCHIVE) {
			$this->storage->ensureMailboxExists($this->archive_mailbox);
		}

		if ($this->mode == self::MODE_READ) {
			$this->messages = $this->storage->searchUnseen();
		} else {
			$this->messages = $this->storage->searchIds($this->fetch_limit);
		}

		return $this->storage;
	}


	/**
	 * Gets the message storage
	 *
	 * @return \Application\DeskPRO\EmailGateway\Storage\Imap
	 */
	public function getStorage()
	{
		return $this->storage;
	}


	/**
	 * Gets the next message
	 * Iterates over the fetched IDs and retrieves the next message in list
	 *
	 * @return \Fetch\Message The next Message
	 */
	public function getNextMessage()
	{
		return $this->storage->getMessageByUid($this->messages[$this->next_id]);
	}


	/**
	 * {@inheritdoc}
	 * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
	 */
	public function _readNext()
	{
		$message = $this->getNextMessage();

		$raw_message = new RawMessage();
		$raw_message->id   = $message->getId();
		$raw_message->uid  = $message->getId();
		$raw_message->size = $message->getOverview()->size;

		if ($this->max_size && $raw_message->size && $raw_message->size > $this->max_size) {
			// If we are here, it means that message is larger than the max size
			// So, we won't store the whole message, only the headers.
			$raw_message->content = $this->storage->getRawHeaders($message);
		} else {
			// Otherwise store the whole message
			$raw_message->content = $this->storage->getRawMessage($message->getUid());
		}

		$headers = null;

		$this->logger->log(sprintf("Message size: %s bytes", $raw_message->size), 'debug');

		if ($raw_message->uid) {
			$this->logger->log(sprintf("Message UID: %s", $raw_message->uid), 'debug');
		}

		$EOL = "\n";

		// Reads and formats the Message header
		// To be compatible with the RawMessage
		if (strpos($raw_message->content, $EOL . $EOL)) {
			list($headers, ) = explode($EOL . $EOL, $raw_message->content, 2);
		} else if ($EOL != "\r\n" && strpos($raw_message->content, "\r\n\r\n")) {
			list($headers, ) = explode("\r\n\r\n", $raw_message->content, 2);
		} else if ($EOL != "\n" && strpos($raw_message->content, "\n\n")) {
			list($headers, ) = explode("\n\n", $raw_message->content, 2);
		} else {
			@list($headers, ) = @preg_split("%([\r\n]+)\\1%U", $raw_message->content, 2);
		}

		$raw_message->headers = $headers;

		$this->next_id++;

		return $raw_message;
	}


	/**
	 * Processes the message after reading it.
	 * Moves it to the DP_Mailbox folder marking it "read"
	 *
	 * @param int $id ID of the message
	 */
	public function _doneRead($id)
	{
		$message = $this->storage->getMessage($id);

		if ($this->mode === self::MODE_PRESERVE) {
			return $message->moveToMailBox($this->archive_mailbox);
		}

		if ($this->mode === self::MODE_AGGRESIVE) {
			return $message->delete();
		}
	}
}