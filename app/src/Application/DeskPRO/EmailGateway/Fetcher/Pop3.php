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

		$this->logger->log("Connecting {$options['user']}@{$options['host']}:{$options['port']}", 'debug');

		if (isset($this->gateway['connection_options']['secure']) AND $this->gateway['connection_options']['secure']) {
			$options['ssl'] = strtoupper($this->gateway['connection_options']['secure']); // 'ssl' or 'tls'
			$this->logger->log('SSL Enabled', 'debug');
		}

		$storage = new \Zend\Mail\Storage\Pop3($options);
		return $storage;
	}

	/**
	 * Reads the next message in the inbox
	 *
	 * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
	 */
	protected function _readNext()
	{
		$this->getStorage();// init connection

		$this->read_count++;
		$this->logger->log("Trying to read next ({$this->read_count} call)", 'debug');

		$start_time = microtime(true);

		try {
			$headers = $this->getStorage()->getRawHeader(1);
		} catch (\Zend\Mail\Protocol\Exception $e) {
			// means there is none
			$this->logger->log("No more messages", 'debug');
			return null;
		} catch (\Exception $e) {
			$this->logger->log("Exception: {$e->getMessage()} {$e->getTraceAsString()}", 'crit');
			throw $e;
		}

		$raw_message = new RawMessage();
		$raw_message->id = 1;
		$raw_message->headers = $headers;
		$raw_message->content = $headers . "\n\n" . $this->getStorage()->getRawContent(1);

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
		} catch (\Zend\Mail\Protocol\Exception $e) {
			$this->logger->log("-- Result: {$e->getCode()} {$e->getMessage()}", 'debug');
			/* usually reading a pop message marks it for deletion, which
			 throws an -ERR. So we'll ignore it
			 */
		} catch (\Exception $e) {
			$this->logger->log("Exception: {$e->getMessage()} {$e->getTraceAsString()}", 'crit');
			throw $e;
		}
	}

	public function test()
	{
		return true;
	}
}
