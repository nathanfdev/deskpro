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
 * Fetches mail from a pop3 server
 */
class Pop3 extends AbstractFetcher
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
	 * Initiates the connection
	 *
	 * @return \Zend\Mail\Storage\Pop3
	 */
	protected function _initConnection()
	{
		$options = array();
		$options['host']     = isset($this->gateway['connection_options']['host'])     ? $this->gateway['connection_options']['host']     : 'localhost';
		$options['port']     = isset($this->gateway['connection_options']['port'])     ? $this->gateway['connection_options']['port']     : '110';
		$options['user']     = isset($this->gateway['connection_options']['username']) ? $this->gateway['connection_options']['username'] : '';
		$options['password'] = isset($this->gateway['connection_options']['password']) ? $this->gateway['connection_options']['password'] : '';

		$this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');

		if (isset($this->gateway['connection_options']['secure']) AND $this->gateway['connection_options']['secure']) {
			$options['ssl'] = strtoupper($this->gateway['connection_options']['secure']); // 'ssl' or 'tls'
			$this->logger->log('SSL Enabled', 'debug');
		}

		$options['logger'] = $this->logger;

		$storage = new \Application\DeskPRO\EmailGateway\Storage\Pop3($options);
		return $storage;
	}

	public function close()
	{
		if ($this->storage) {
			$this->storage->close();
			$this->storage = null;
		}
	}

	/**
	 * Server supports uniqid?
	 *
	 * @return mixed
	 */
	protected function canUniqueId()
	{
		static $can = null;

		if ($can === null) {
			$can = $this->getStorage()->canUniqueId();
		}

		return $can;
	}

	/**
	 * Get a list of message IDs
	 */
	protected function _initMessageList($reload = false)
	{
		if (!$reload && $this->message_list !== null) {
			return;
		}

		if ($this->gateway->keep_read) {
			if (!$this->canUniqueId()) {
				$this->logger->log("Gateway does not support unique but keep_read is enabled. Capabilities: " . implode(', ', $this->getStorage()->getProtocolCapabilities()), 'debug');

				$e = new \InvalidArgumentException("Gateway does not support uniqueid");
				$einfo = \DeskPRO\Kernel\KernelErrorHandler::getExceptionInfo($e);
				$einfo['no_send_error'] = true;
				\DeskPRO\Kernel\KernelErrorHandler::logErrorInfo($einfo);

				$this->message_list = array();
				return;
			}

			$id_to_num = array_flip($this->getStorage()->getUniqueId());

			$this->logger->log("Server has " . count($id_to_num) . " messages", 'debug');

			$read_ids = App::getDb()->fetchAllCol("
				SELECT id
				FROM email_uids
				WHERE gateway_id = ?
			", array($this->gateway->getId()));

			$this->logger->log("System has " . count($read_ids) . " tracked IDs", 'debug');

			foreach ($read_ids as $id) {
				unset($id_to_num[$id]);
			}

			$this->message_list_ids  = array_flip($id_to_num);

			$list = $this->getStorage()->getSize();

			$this->message_list = array();
			foreach ($list as $num => $size) {
				if (isset($this->message_list_ids[$num])) {
					$this->message_list[] = array('num' => $num, 'size' => $size, 'uid' => $this->message_list_ids[$num]);
				}
			}

			$this->logger->log("Message list contains " . count($this->message_list) . " messages", 'debug');

		} else {
			$list = $this->getStorage()->getSize();

			$this->message_list = array();
			foreach ($list as $num => $size) {
				$this->message_list[] = array('num' => $num, 'size' => $size, 'uid' => null);
			}

			$this->logger->log("Message list contains " . count($this->message_list) . " messages", 'debug');
		}
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

		if (!$message_id && $this->canUniqueId()) {
			try {
				$message_id = $this->getStorage()->getProtocol()->uniqueid($message_num);
			} catch (\Exception $e) {}
		}

		$start_time = microtime(true);

		$this->logger->log("Fetching message $message_num", 'debug');

		$raw_message = new RawMessage();
		$raw_message->id   = $message_num;
		$raw_message->uid  = $message_id;
		$raw_message->size = $message_size;

		if ($this->max_size && $raw_message->size && $raw_message->size > $this->max_size) {
			$raw_message->content = $this->getStorage()->getProtocol()->top($message_num) . "\n\n";
		} else {
			$raw_message->content = $this->getStorage()->getProtocol()->retrieve($message_num);
		}
		$headers = null;

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
		if ($this->gateway->keep_read) {
			return;
		}

		$this->logger->log("Marking message as deleted: $id", 'debug');
		try {
			$this->getStorage()->removeMessage($id);
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
			$x = $this->getStorage()->countMessages();
		} catch (\Exception $e) {
			throw $e;
		}

		return $x;
	}
}
