<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Fetches mail from a pop3 server
 */
class Pop3 extends AbstractFetcher
{
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

		if (isset($this->gateway['connection_options']['secure']) AND $this->gateway['connection_options']['secure']) {
			$options['ssl'] = strtoupper($this->gateway['connection_options']['secure']); // 'ssl' or 'tls'
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
		try {
			$headers = $this->storage->getRawHeader(1);
		} catch (\Zend\Mail\Protocol\Exception $e) {
			// means there is none
			return null;
		}

		$raw_message = new RawMessage();
		$raw_message->id = 1;
		$raw_message->headers = $headers;
		$raw_message->content = $headers . "\n\n" . $this->storage->getRawContent(1);

		return $raw_message;
	}

	/**
	 * Deletes the message from the server.
	 *
	 * @param  $id
	 */
	protected function _doneRead($id)
	{
		try {
			$this->storage->removeMessage($id);
		} catch (\Zend\Mail\Protocol\Exception $e) {
			/* usually reading a pop message marks it for deletion, which
			 throws an -ERR. So we'll ignore it
			 */
		}
	}

	public function test()
	{
		return true;
	}
}
