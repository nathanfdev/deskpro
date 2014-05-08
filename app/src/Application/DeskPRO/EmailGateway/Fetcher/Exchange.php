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
 * Fetches mail from an exchange server
 */
class Exchange extends AbstractFetcher
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
	 * @var \Application\DeskPRO\EmailGateway\Storage\Exchange
	 */
	protected $storage;

	/**
	 * Messages retrieved in the current fetch
	 *
	 * @var Array An array of messages
	 */
	protected $messages;

	/**
	 * Mailbox name to move messages after processing
	 * @var String Mailbox name
	 */
	private $archive_mailbox;

	/**
	 * Mailbox name to read messages from
	 * @var String Mailbox name
	 */
	private $read_mailbox;

	/**
	 * Max number of email IDs to fetch in one go
	 * @var int
	 */
	protected $fetch_limit = 100;

	/**
	 * Next Message index to read
	 *
	 * @var int
	 */
	protected $next_index = 0;


	/**
	 * Initiates the connection
	 *
	 * @return \Zend\Mail\Storage\Pop3
	 */
	protected function _initConnection()
	{
		$options = array();

		switch ($this->account->incoming_account->getType()) {
			case 'exchange':
				/** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ExchangeConfig $exchange_config */
				$exchange_config = $this->account->incoming_account;

				$options['host']         = $exchange_config->host;
				$options['port']         = $exchange_config->port;
				$options['user']         = $exchange_config->user;
				$options['password']     = $exchange_config->password;
				$options['mode']         = $exchange_config->mode;
				$options['read_mailbox'] = $exchange_config->read_mailbox;

				if ($exchange_config->mode == self::MODE_ARCHIVE) {
					$options['archive_mailbox'] = $exchange_config->archive_mailbox;
				}

				break;

			default:
				throw new \InvalidArgumentException("Unknown account type: " . $this->account->incoming_account->getType());
		}

		$this->mode = $options['mode'];
		$this->archive_mailbox = !empty($options['archive_mailbox']) ? $options['archive_mailbox'] : 'DP_Archive';
		$this->read_mailbox    = !empty($options['read_mailbox']) ? $options['read_mailbox'] : null;

		$this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');
		$options['logger'] = $this->logger;

		$this->storage = new Storage\Exchange($options);

		if ($this->mode == self::MODE_ARCHIVE) {
			$this->storage->ensureFolderExists($this->archive_mailbox);
		}

		if ($this->read_mailbox) {
			$this->storage->ensureFolderExists($this->read_mailbox);
		}

		$unread_only = false;
		$folder = null;

		if ($this->mode == self::MODE_READ) {
			$unread_only = true;
		}
		if ($this->read_mailbox) {
			$folder = $this->read_mailbox;
		}

		$this->messages = $this->storage->searchIds($this->fetch_limit, $unread_only, $folder);
		if (!$this->messages) {
			$this->messages = array();
		}

		$this->logger->log("Read %d messages", count($this->messages));

		return $this->storage;
	}


	/**
	 * Gets the message storage
	 *
	 * @return \Application\DeskPRO\EmailGateway\Storage\Exchange
	 */
	public function getStorage($reconnect = false)
	{
		return $this->storage;
	}


	/**
	 * Gets the next message
	 * Iterates over the fetched IDs and retrieves the next message in list
	 *
	 * @return object
	 */
	public function getNextMessage()
	{
		return $this->storage->getEmailParts($this->messages[$this->next_index]);
	}


	/**
	 * {@inheritdoc}
	 * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
	 */
	public function _readNext()
	{
		if (!$this->storage) {
			$this->_initConnection();
		}
		
		$message_id = $this->messages[$this->next_index];
		$this->next_index++;

		$message = $this->storage->getEmailProps($message_id);

		$raw_message = new RawMessage();
		$raw_message->id   = $message_id;
		$raw_message->uid  = $message_id;
		$raw_message->size = $message->size;

		if ($this->max_size && $raw_message->size && $raw_message->size > $this->max_size) {
			// If we are here, it means that message is larger than the max size
			// So, we won't store the whole message, only the headers.
			$raw_message->content = $this->storage->getRawHeaders($message_id);
		} else {
			// Otherwise store the whole message
			$raw_message->content = $this->storage->getRawMessage($message_id);
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
		switch ($this->mode) {
			case self::MODE_DELETE:
				$this->storage->deleteMessage($id);
				break;
			case self::MODE_ARCHIVE:
				$this->storage->moveMessage($id, $this->archive_mailbox);
				break;
			case self::MODE_READ:
				$this->storage->markRead($id);
				break;
		}
	}
}