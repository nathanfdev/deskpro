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
	 * Initiates the connection
	 *
	 * @return \Zend\Mail\Storage\Pop3
	 */
	protected function _initConnection()
	{
		$options = array();
		$options['host']     = $this->gateway['connection_options']['host'];
		$options['port']     = $this->gateway['connection_options']['port'];
		$options['user']     = $this->gateway['connection_options']['username'];
		$options['password'] = $this->gateway['connection_options']['password'];

		$this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');

		if (isset($this->gateway['connection_options']['secure']) AND $this->gateway['connection_options']['secure']) {
			$options['ssl'] = strtoupper($this->gateway['connection_options']['secure']); // 'ssl' or 'tls'
			$this->logger->log('SSL Enabled', 'debug');
		}

		$storage = new \Application\DeskPRO\EmailGateway\Storage\Pop3($options);
		return $storage;
	}

	/**
	 * Get a list of message IDs
	 */
	protected function _initMessageList($reload = false)
	{
		if (!$reload && $this->message_list !== null) {
			return;
		}

		$list = $this->getStorage()->getSize();

		$this->message_list = array();
		foreach ($list as $num => $size) {
			$this->message_list[] = array('num' => $num, 'size' => $size);
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

		$start_time = microtime(true);

		$this->logger->log("Fetching message $message_num", 'debug');

		$raw_message = new RawMessage();
		$raw_message->id = $message_num;
		$raw_message->size = $message_size;

		if (!$this->max_size || $raw_message->size < $this->max_size) {
			$raw_message->content = $this->getStorage()->getProtocol()->retrieve($message_num);
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
		} else {
			$raw_message->too_big = true;
		}

		$this->logger->log(sprintf("Got message [1]. Took %0.2f seconds.", microtime(true) - $start_time), 'debug');

		return $raw_message;
	}

	/**
	 * Deletes the message from the server.
	 *
	 * @param  $id
	 */
	protected function _doneRead($id)
	{
		$this->logger->log("Marking message as deleted: $id", 'debug');
		try {
			$this->getStorage()->removeMessage($id);
		} catch (\Exception $e) {
			$this->logger->log("Exception: {$e->getMessage()} {$e->getTraceAsString()}", 'crit');
			throw $e;
		}
	}

	public function test()
	{
		try {
			$x = $this->getStorage()->countMessages();
		} catch (\Exception $e) {
			throw $e;
		}

		return true;
	}
}
