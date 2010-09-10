<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Queue
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Queue\Adapter;

use \Orb\Util\Strings;
use \Orb\Util\Util;
use \Application\CoreBundle\Entity\QueueItem;


use \Zend\Queue\Queue;
use \Zend\Queue\Exception as QueueException;
use \Zend\Queue\Message;

/**
 * Adapter to use the QueueItemEntity
 */
class QueueItemEntity extends \Zend\Queue\Adapter\AbstractAdapter
{
	/**
	 * Entity manager
	 * @var DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * Plain database connection for raw queries
	 * @var DeskPRO\DBAL\Connection
	 */
	protected $db;

	public function __construct($options, Queue $queue = null)
	{
		parent::__construct($options, $queue);

		$this->em = $options['em'];
		$this->db = $options['db'];

		$this->_queues = null;
	}



	/**
	 * Check to see if a queue exists.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isExists($name)
	{
		if ($this->_queues === null) {
			$this->getQueues();
		}

		return in_array($name, $this->_queues);
	}

	

	/**
	 * Get an array of queues
	 * @return array
	 */
	public function getQueues()
	{
		if ($this->_queues === null) {
			$this->_queues = $this->db->fetchAllCol("SELECT DISTINCT(groupname) FROM queue_item");
		}

		return $this->_queues;
	}


	/**
	 * Create a new queue. Always works because we dont create queue per-se; to create
	 * a queue you just insert a job with the groupname name you want.
	 *
	 * @param string $name
	 * @param int $timeout
	 * @return bool
	 */
	public function create($name, $timeout=null)
	{
		$this->_queues[] = $name;

		return true;
	}


	
	/**
	 * Delete a queue and all jobs in it
	 */
	public function delete($name)
	{
		$this->db->delete('queue_item', array('groupname' => $name));

		return true;
	}
	


	/**
	 * Get how many jobs belong to a queue
	 *
	 * @param Queue\Queue $queue
	 * @return int
	 */
	public function count(Queue\Queue $queue=null)
	{
		return $this->db->fetchColumn("SELECT COUNT(*) FROM queue_item WHERE groupname = ?", array($queue->getName()));
	}


	
	/**
	 * Put a job onto the queue.
	 *
	 * @param string $message
	 * @param Queue\Queue $queue
	 * @return classname
	 */
	public function send($message, Queue\Queue $queue=null)
	{
		if ($queue === null) {
			$queue = $this->_queue;
		}

		$item = $this->em->createEntity('CoreBundle:QueueItem');
		$item['groupname'] = $queue->getName();
		$item['data'] = $message;

		$this->em->persist($item);
		$this->em->flush();

		if ($queue AND $this->_current_used_tube != $queue->getName()) {
			$this->_current_used_tube = $queue->getName();
			$this->_pheanstalk->useTube($queue->getName());
		}

		$this->_pheanstalk->put((string)$message);

		$options = array(
			'queue' => $queue,
			'data'  => $item->toArray(),
		);
		$classname = $queue->getMessageClass();
		return new $classname($options);
	}



	/**
	 * Reserve one or more jobs from the queue.
	 *
	 * @param int $maxMessages
	 * @param int $timeout
	 * @param Queue\Queue $queue
	 * @return classname
	 */
	public function receive($maxMessages=null, $timeout=null, Queue\Queue $queue=null)
	{
		if ($maxMessages === null) {
			$maxMessages = 1;
		}

		if ($timeout === null) {
			$timeout = self::RECEIVE_TIMEOUT_DEFAULT;
		}
		if ($queue === null) {
			$queue = $this->_queue;
		}

		$msgs = array();
		if ($maxMessages > 0 ) {

			$timenow = new \DateTime();
			$results = $this->em->createQuery("
				SELECT *
				FROM CoreBundle:QueueItem i
				WHERE
					(i.is_ready = ? AND (i.reserved_at IS NULL OR i.timeout_at < ?))
					AND (i.delay_until IS NULL OR i.delay_until < ?)
				ORDER BY i.priority
				LIMIT ?
			")->setParameters(array(true, $timenow, $timenow));

			foreach ($results as $item) {
				$msgs[] = $item->toArray();

				$item['reserved_at'] = $timenow;
				$item['timeout_at'] = $timenow->add(new \DateInterval('PT' . $item['ttr'] . 'S'));
				$this->em->persist($item);
			}

			$this->em->flush();
		}

		$options = array(
			'queue'        => $queue,
			'data'         => $msgs,
			'messageClass' => $queue->getMessageClass(),
		);
		$classname = $queue->getMessageSetClass();
		return new $classname($options);
	}


	/**
	 * Delete a message from the queue
	 * @param Message $message
	 */
	public function deleteMessage(Message $message)
	{
		$this->db->delete('queue_item', array('id' => $message->id));
		return true;
	}
}