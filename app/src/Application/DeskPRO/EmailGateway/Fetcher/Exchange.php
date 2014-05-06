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

/**
 * Fetches mail from a imap server
 */
class Exchange extends AbstractFetcher
{
	/**
	 * The defualt mode
	 *
	 * It makes the message "READ" after processing it
	 */
	const MODE_DEFAULT		= 1;

	/**
	 * The aggresive mode
	 *
	 * Processes the message and then immediately deletes it
	 */
	const MODE_AGGRESIVE	= 2;

	/**
	 * The preserving mode
	 *
	 * It preserves all the messages, after processinga message moves it to a
	 * specified folder
	 */
	const MODE_PRESERVE		= 3;

	/**
	 * The IMAP Storage
	 *
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
	protected $dpMailboxName;

	/**
	 * Max number of email IDs to fetch in one go
	 * @var int
	 */
	protected $fetchLimit = 10;

	/**
	 * Next Message index to read
	 *
	 * @var int
	 */
	protected $nextId = 0;

	protected $mode = self::MODE_DEFAULT;

	/**
	 * Init function
	 * Sets the memory protection size
	 */
	public function init()
	{
		$this->memory_protection_size = 3670016;
	}

	/**
	 * Initiates the connection
	 *
	 * @return \Zend\Mail\Storage\Pop3
	 */
	protected function _initConnection()
	{
		/**
		 * As the system is not ready yet,
		 * let's just hard code them
		 */
		$server		 = 'connect.emailsrvr.com';
		$username	 = 'abhinav@deskpro.tv';
		$password	 = 'P@ssw0rd';
		$mode		 = self::MODE_PRESERVE;
		$dpProcessed = 'DP_Processed';

		//		$options['host']     = isset($this->gateway['connection_options']['host'])     ? $this->gateway['connection_options']['host']     : 'localhost';
		//		$options['port']     = isset($this->gateway['connection_options']['port'])     ? $this->gateway['connection_options']['port']     : '110';
		//		$options['user']     = isset($this->gateway['connection_options']['username']) ? $this->gateway['connection_options']['username'] : '';
		//		$options['password'] = isset($this->gateway['connection_options']['password']) ? $this->gateway['connection_options']['password'] : '';

		$this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');

		//		if (isset($this->gateway['connection_options']['secure']) AND $this->gateway['connection_options']['secure']) {
		//			$options['ssl'] = strtoupper($this->gateway['connection_options']['secure']); // 'ssl' or 'tls'
		//			$this->logger->log('SSL Enabled', 'debug');
		//		}

		$options['logger'] = $this->logger;

		$this->mode = $mode;

		$this->storage = new \Application\DeskPRO\EmailGateway\Storage\Exchange($server, $username, $password, $this->mode, $dpProcessed);

		if (self::MODE_PRESERVE === $this->mode) {
			if ($this->dpMailboxName === $this->storage->getMailbox()) {
				throw new \Exception("The current mailbox is reserved for processed emails, it can not be used as the primary mailbox");
			}

			$this->createDPMailbox();
		}

		$this->fetch();

		return $this->storage;
	}

	/**
	 * Checks and creates a mailbox on the server for processed emails
	 */
	public function createDPMailbox()
	{
		if (!$this->storage->findFolder($this->dpMailboxName)) {
			$this->storage->createFolder($this->dpMailboxName);
		}
	}

	/**
	 * Fetches the Message IDs for next run
	 * reads from the main mailbox (INBOX)
	 *
	 * @return \Application\DeskPRO\EmailGateway\Fetcher\Imap2
	 */
	public function fetch()
	{
		$this->messages = $this->storage->searchIds($this->fetchLimit);

		return $this;
	}

	/**
	 * Gets the message storage
	 *
	 * @return \Application\DeskPRO\EmailGateway\Storage\Imap2
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
		return $this->storage->getEmailParts($this->messages[$this->nextId]);
	}

	/**
	 * {@inheritdoc}
	 * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
	 */
	public function _readNext()
	{
		$message = $this->getNextMessage();

		$raw_message = new RawMessage();
		$raw_message->id   = $message->ItemId->Id;
		$raw_message->uid  = $message->ItemId->Id;
		$raw_message->size = $message->size;

		if ($this->max_size && $raw_message->size && $raw_message->size > $this->max_size) {
			// If we are here, it means that message is larger than the max size
			// So, we won't store the whole message, only the headers.
			$raw_message->content = $this->storage->getRawHeaders($this->messages[$this->nextId]);
		} else {
			// Otherwise store the whole message
			$raw_message->content = $this->storage->getEmailParts($this->messages[$this->nextId]);
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

		$this->nextId++;

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
		$message = new \stdClass();

		@$message->ItemId->Id = $id;

		$message = $this->storage->getEmailParts($message);

		if (self::MODE_PRESERVE === $this->mode) {
			return $this->storage->moveMessage($message, $this->dpMailboxName);
		}

		if (self::MODE_AGGRESIVE === $this->mode) {
			return $this->storage->deleteMessage($message);
		}
	}
}