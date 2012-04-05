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
	 * @var \Zend\Mail\Storage\AbstractStorage
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
	 * @return \Zend\Mail\Storage\AbstractStorage
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
	 * @return \Zend\Mail\Storage\AbstractStorage
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
