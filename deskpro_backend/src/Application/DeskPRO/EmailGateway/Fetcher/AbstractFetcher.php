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
	 * @var \Zend\Mail\AbstractStorage
	 */
	protected $storage;

	/**
	 * @var \Application\DeskPRO\Log\Logger
	 */
	protected $logger;

	public function __construct(Entity\EmailGateway $gateway)
	{
		$this->gateway = $gateway;
		$this->logger = new \Application\DeskPRO\Log\Logger();
	}

	/**
	 * @return \Zend\Mail\AbstractStorage
	 */
	public function getStorage()
	{
		if (!$this->storage) {
			$this->storage = $this->_initConnection();
		}

		return $this->storage;
	}

	/**
	 * @param $logger \Application\DeskPRO\Log\Logger
	 */
	public function setLogger(\Application\DeskPRO\Log\Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Initiates the connection
	 * @return \Zend\Mail\AbstractStorage
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

			$source = new Entity\EmailSource();
			$source->fromArray(array(
				'gateway' => $this->gateway,
				'headers' => $raw_message->headers,
				'status' => 'inserted'
			));

			$desc = App::getSystemService('filestorage')->createRandomPath();
			$desc->write($raw_message->content, array(
				'filename' => 'email.dat',
			));

			$blob_id = $desc->getPath();
			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$source->blob = $blob;

			App::getOrm()->persist($source);
			App::getOrm()->flush();

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


	/**
	 * Test the resource to see if configuration is correct and/or that the service
	 * supports the required features.
	 *
	 * @return bool
	 */
	public function test()
	{
		return true;
	}
}
