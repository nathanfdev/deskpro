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
 * Fetches mail from an imap server
 */
class Imap extends AbstractFetcher
{
	/**
	 * Initiates the connection
	 *
	 * @return \Zend_Mail_Storage_Imap
	 */
	protected function _initConnection()
	{
		$storage = new \Zend_Mail_Storage_Imap($this->gateway['connection_options']);
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
		} catch (\Zend_Mail_Storage_Exception $e) {
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
		$move_to = isset($this->gateway['connection_options']['delete_move']) ? $this->gateway['connection_options']['delete_move'] : false;
		if ($move_to) {
			$this->storage->moveMessage($id, $move_to);
		} else {
			$this->storage->removeMessage($id);
		}
	}
}
