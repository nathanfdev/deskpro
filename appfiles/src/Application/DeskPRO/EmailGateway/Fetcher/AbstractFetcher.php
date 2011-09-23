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
 * A fetcher takes makes a conenction to a resource described in
 * an EmailGateway record, and reads messages into the database for storage.
 */
abstract class AbstractFetcher
{
	/**
	 * \Application\DeskPRO\Entity\EmailGateway
	 */
	protected $gateway;

	/**
	 * @var \Zend_Mail_Storage_Abstract
	 */
	protected $storage;

	public function __construct(Entity\EmailGateway $gateway)
	{
		$this->gateway = $gateway;
		$this->storage = $this->_initConnection();
	}

	/**
	 * Initiates the connection
	 * @return \Zend_Mail_Storage_Abstract
	 */
	abstract protected function _initConnection();

	/**
	 * Reads the next message in the inbox. Must return
	 * a RawMessage.
	 *
	 * Return null if there are no more messages.
	 *
	 * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
	 */
	abstract protected function _readNext();

	/**
	 * Marks the email as 'read' in any way that'll prevent the system from
	 * reading it again. For example, deleting it, moving it to a new folder, etc.
	 *
	 * @param  $id
	 */
	abstract protected function _doneRead($id);


	/**
	 * Reads the next message from the resource and saves it into the datbaase,
	 * then removes the email so its not read again.
	 *
	 * Returns null if there are no more messages.
	 *
	 * @return \Application\DeskPRO\Entity\EmailSource
	 */
	public function readNext()
	{
		$raw_message = $this->_readNext();
		if (!$raw_message) {
			return null;
		}

		App::getOrm()->beginTransaction();

		try {
			#------------------------------
			# Store the message
			#------------------------------

			$db = App::getDb();

			$source = new Entity\EmailSource();
			$source->fromArray(array(
				'gateway' => $this->gateway,
				'headers' => $raw_message->headers,
				'status' => 'inserted'
			));

			App::getOrm()->persist($source);
			App::getOrm()->flush();

			$data_len = strlen($raw_message->content);

			// /2 for worst-case scenario of every character needing escape, -200 for wiggle room fo rest of query
			$max_size = ($db->getMaxPacketSize()/2)-200;
			$parts = ceil($data_len / $max_size);

			for ($i = 0; $i < $parts; $i++) {
				$db->insert('email_sources_blobs', array(
					'source_id' => $source['id'],
					'data' => substr($raw_message->content, $i * $max_size, $max_size)
				));
			}

			App::getOrm()->commit();

			#------------------------------
			# Delete message on the server
			#------------------------------

			$this->_doneRead($raw_message->id);

		} catch (\Exception $e) {
			App::getOrm()->rollback();
			throw $e;
		}

		$source->_raw = $raw_message->content;

		return $source;
	}
}
