<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Mail
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Mail\Transport;

use \Orb\Mail\QueueProcessor\QueueProcessorInterface;
use \Orb\Mail\Message;

use \Orb\Util\Strings;
use \Orb\Util\Util;

/**
 * Queue mail transport
 */
class QueueTransport extends Swift_Transport
{
	protected $_queue_processor;
	protected $_event_dispatcher;

	public function __construct(QueueProcessorInterface $queue_processor, \Swift_Events_EventDispatcher $event_dispatcher)
	{
		$this->_queue_processor = $queue_processor;
		$this->_event_dispatcher = $event_dispatcher;
	}

	public function getQueueProcessor()
	{
		return $this->_queue_processor;
	}

	public function isStarted()
	{
		return true;
	}

	public function start()
	{
		$this->_queue_processor->startQueue();
	}

	public function stop()
	{
		$this->_queue_processor->shutdownQueue();
	}

	public function send(Message $message, &$failedRecipients = null)
	{
		if ($evt = $this->_event_dispatcher->createSendEvent($this, $message)) {
			$this->_event_dispatcher->dispatchEvent($evt, 'beforeSendPerformed');
			if ($evt->bubbleCancelled()) {
				return 0;
			}
		}

		$success = $this->_queue_processor->queueMessage($message);

		if ($evt) {
			$evt->setResult($success ? \Swift_Events_SendEvent::RESULT_SUCCESS : \Swift_Events_SendEvent::RESULT_FAILED);
			$this->_event_dispatcher->dispatchEvent($evt, 'sendPerformed');
		}

		return 1;
	}

	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
		$this->_eventDispatcher->bindEventListener($plugin);
	}
}