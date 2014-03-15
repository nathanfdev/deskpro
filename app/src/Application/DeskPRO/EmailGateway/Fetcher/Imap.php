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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Fetches mail from a imap server
 */
class Imap extends AbstractFetcher
{
	protected $read_count = 0;

	/**
	 * @var array
	 */
	protected $message_list = null;

	/**
	 * @var array
	 */
	protected $message_list_ids = array();

	/**
	 * @var array
	 */
	protected $message_files = array();

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
		$options = array();
		$options['host']     = isset($this->account['connection_options']['host'])     ? $this->account['connection_options']['host']     : 'localhost';
		$options['port']     = isset($this->account['connection_options']['port'])     ? $this->account['connection_options']['port']     : '110';
		$options['user']     = isset($this->account['connection_options']['username']) ? $this->account['connection_options']['username'] : '';
		$options['password'] = isset($this->account['connection_options']['password']) ? $this->account['connection_options']['password'] : '';

		$this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');

		if (isset($this->account['connection_options']['secure']) AND $this->account['connection_options']['secure']) {
			$options['ssl'] = strtoupper($this->account['connection_options']['secure']); // 'ssl' or 'tls'
			$this->logger->log('SSL Enabled', 'debug');
		}

		$options['logger'] = $this->logger;

		$storage = new \Application\DeskPRO\EmailGateway\Storage\Imap($options);
		return $storage;
	}


	/**
	 * Closes the connection
	 */
	public function close()
	{
		if ($this->storage) {
			$this->storage->close();
			$this->storage = null;
		}
	}


	/**
	 * @return \Application\DeskPRO\EmailGateway\Storage\Imap
	 */
	public function getStorage($reconnect = false)
	{
		return parent::getStorage($reconnect);
	}


	/**
	 * Get a list of message IDs
	 */
	protected function _initMessageList($reload = false)
	{
		if (!$reload && $this->message_list !== null) {
			return;
		}

		$this->message_list = array();

		$message_ids = $this->getStorage()->getUnseenMessageUids();
		if ($message_ids) {
			return;
		}

		$message_sizes = $this->getStorage()->getMessageSizesByUids($message_ids);
		foreach ($message_sizes as $uid => $size) {
			$this->message_list[] = array('num' => $uid, 'size' => $size, 'uid' => null);
		}

		$this->logger->log("Message list contains " . count($this->message_list) . " messages", 'debug');
	}

	/**
	 * Reads the next message in the inbox
	 *
	 * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
	 */
	protected function _readNext()
	{
		$this->getStorage();
		$this->_initMessageList();

		$this->read_count++;
		$this->logger->log("Trying to read next ({$this->read_count} call)", 'debug');

		$next = array_shift($this->message_list);
		if (!$next) {
			return null;
		}

		$message_size = $next['size'];
		$message_num  = $next['num'];
		$message_id   = $next['uid'];

		$start_time = microtime(true);

		$this->logger->log("Fetching message #$message_num", 'debug');

		$raw_message = new RawMessage();
		$raw_message->id   = $message_num;
		$raw_message->uid  = $message_id;
		$raw_message->size = $message_size;

		if ($this->max_size && $raw_message->size && $raw_message->size > $this->max_size) {
			$raw_message->content = $this->getStorage()->getMessageHeadersByUid($message_num);
		} else {
			$raw_message->content = $this->getStorage()->getRawMessageByUid($message_num);
		}
		$headers = null;

		$this->logger->log(sprintf("Message size: %s bytes", $message_size), 'debug');

		if ($raw_message->uid) {
			$this->logger->log(sprintf("Message UID: %s", $raw_message->uid), 'debug');
		}

		$EOL = "\n";
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

		if (!$raw_message->size) {
			$raw_message->size = strlen($raw_message->content);
		}

		if ($this->max_size && $raw_message->size > $this->max_size) {
			$raw_message->too_big = true;
			$this->logger->log("Setting too_big flag", 'debug');
		}

		$this->logger->log(sprintf("Got message %d %s. Took %0.2f seconds.", $message_num, $message_id, microtime(true) - $start_time), 'debug');

		return $raw_message;
	}

	/**
	 * Deletes the message from the server.
	 *
	 * @param  $id
	 */
	protected function _doneRead($id)
	{
		$this->logger->log("Marking message as read: $id", 'debug');
		try {
			$storage->markReadByUids(array($id));
		} catch (\Exception $e) {
			$this->logger->log("Exception: {$e->getMessage()} {$e->getTraceAsString()}", 'crit');
			throw $e;
		}
	}


	/**
	 * Tests the connection and returns the number of messages on success
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function test()
	{
		try {
			$x = $this->getStorage()->setFlags();
		} catch (\Exception $e) {
			throw $e;
		}

		return $x;
	}
}
