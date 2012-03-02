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
 * Orb
 *
 * @package Orb
 * @subpackage Queue
 */

namespace Orb\Queue\Adapter;

use Orb\Util\Strings;
use Orb\Util\Util;


use Zend\Queue\Queue;
use Zend\Queue\Exception as QueueException;
use Zend\Queue\Message;

/**
 * Beanstalkd adapter
 *
 * @see http://github.com/pda/pheanstalk
 * @see http://github.com/kr/beanstalkd
 */
class Beanstalkd extends \Zend\Queue\Adapter\AbstractAdapter
{
	/**
	 * @var Pheanstalk
	 */
	protected $_pheanstalk;

	/**
	 * Was the beanstalkd connection opened automatically by this obj?
	 * @var bool
	 */
	protected $_auto_connected = false;

	/**
	 * The tubename we're currently using to put jobs into
	 * @var string
	 */
	protected $_current_used_tube = 'default';

	/**
	 * The tubes we're currently 'watching'
	 * @var array
	 */
	protected $_current_watched_tubes = array('default');
	
	public function __construct($options, Queue\Queue $queue = null)
	{
		parent::__construct($options, $queue);

		$this->_queues = null;

		if (isset($options['driverOptions'])) {
			$this->_pheanstalk = new \Pheanstalk(
				$options['driverOptions']['host'],
				isset($options['driverOptions']['port']) ? $options['driverOptions']['port'] : \Pheanstalk::DEFAULT_PORT,
				isset($options['driverOptions']['connect_timeout']) ? $options['driverOptions']['connect_timeout'] : null
			);
			$this->_current_watched_tubes = array('default');
			$this->_current_used_tube = 'default';
			$this->_auto_connected = true;
		} elseif (isset($options['pheanstalk']) AND $options['pheanstalk'] instanceof \Pheanstalk) {
			$this->_pheanstalk = $options['pheanstalk'];
			$this->_current_watched_tubes = $this->_pheanstalk->listTubesWatched();
			$this->_current_used_tube = $this->_pheanstalk->listTubeUsed();
		} else {
			throw new \InvalidArgumentException('Need driverOptions to define beanstalkd connection, or existing pheanstalk object');
		}
	}



	/**
	 * Gets capabilities of this adapter
	 * 
	 * @return array
	 */
	public function getCapabilities()
	{
		return array(
			'create'        => true,
			'delete'        => false,
			'send'          => true,
			'receive'       => true,
			'deleteMessage' => false,
			'getQueues'     => true,
			'count'         => true,
			'isExists'      => true,
		);
	}


	
	/**
	 * Check to see if a queue (tube) exists. In beanstalkd this means there's at least one
	 * job with in it.
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
	 * Create a new tube. Always works because beanstalkd doesn't create tubes per-se; to create
	 * a tube you just insert a job with the tube name you want.
	 *
	 * @param string $name
	 * @param int $timeout
	 * @return bool
	 */
	public function create($name, $timeout=null)
	{
		if ($this->isExists($name)) {
			return false;
		}

		$this->_queues[] = $name;

		return true;
	}



	/**
	 * Beanstalkd can't delete an entire tube.
	 */
	public function delete($name)
	{
		throw new Queue\Exception('delete() is not supported in this adapter');
	}

	

	/**
	 * Get an array of tubes
	 * @return array
	 */
	public function getQueues()
	{
		if ($this->_queues === null) {
			$this->_queues = $this->_pheanstalk->listTubes();
		}

		return $this->_queues;
	}



	/**
	 * Get how many jobs belong to a queue
	 *
	 * @param Queue\Queue $queue
	 * @return int
	 */
	public function count(Queue\Queue $queue=null)
	{
		$tube = $this->_pheanstalk->statsTube($queue->getName());
		return $tube['total_jobs'];
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

		if ($queue AND $this->_current_used_tube != $queue->getName()) {
			$this->_current_used_tube = $queue->getName();
			$this->_pheanstalk->useTube($queue->getName());
		}

		$job_id = $this->_pheanstalk->put((string)$message);

		$options = array(
			'queue' => $queue,
			'data'  => array('id' => $job_id, 'body' => $message),
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

		// Make sure we're only watching the one we're interested in
		if (count($this->_current_watched_tubes) > 1 OR !in_array($queue->getName(), $this->_current_watched_tubes)) {
			$unwatch = array_diff($this->_current_used_tube, array($queue->getName()));
			$this->_pheanstalk->watch($queue->getName());
			foreach ($unwatch as $x) {
				$this->_pheanstalk->ignore($x);
			}
			$this->_current_watched_tubes = array($queue->getName());
		}

		$msgs = array();
		if ($maxMessages > 0 ) {
			for ($i = 0; $i < $maxMessages; $i++) {
				$job = $this->_pheanstalk->reserve(0);
				if ($job) {
					$data = array(
						'id' => $job['id'],
						'body'   => $job['data'],
					);

					$msgs[] = $data;
				}
			}
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
		$this->_pheanstalk->delete($message->id);
		return true;
	}
}
