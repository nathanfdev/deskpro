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
 * @subpackage Mail
 */

namespace Application\DeskPRO\Mail\Transport;

use Application\DeskPRO\App;

use Application\DeskPRO\Mail\QueueProcessor\Database as DatabaseQueueProcessor;
use Orb\Mail\Transport\QueueTransport;
use Orb\Mail\Message;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * This transport takes care of initializing any other transports based on settings
 * etc, and also queuing.
 */
class DelegatingTransport implements \Swift_Transport
{
	/**
	 * @var bool
	 */
	protected $queue_disabled = false;

	/**
	 * @var \Swift_Events_EventDispatcher
	 */
	protected $event_dispatcher;

	/**
	 * @var array
	 */
	protected $transports = array();

	/**
	 * @var Orb\Mail\Transport\QueueTransport
	 */
	protected $queue_transport = null;

	/**
	 * @param \Swift_Events_EventDispatcher $event_dispatcher
	 */
	public function __construct(\Swift_Events_EventDispatcher $event_dispatcher)
	{
		$this->event_dispatcher = $event_dispatcher;
	}


	/**
	 * Disable the use of the queue, all messages are sent instantly.
	 * Note that messages are still saved in the event of a send failure.
	 */
	public function disableQueue()
	{
		$this->queue_disabled = true;
	}


	/**
	 * Enable the queue.
	 */
	public function enableQueue()
	{
		$this->queue_disabled = false;
	}


	/**
	 * Is queueing currently enabeld?
	 *
	 * @return bool
	 */
	public function isQueueEnabled()
	{
		return !$this->queue_disabled;
	}


	/**
	 * Get the queue transport with the database queue processor.
	 * The queue will be created if it has not already been.
	 *
	 * @return Orb\Mail\Transport\QueueTransport
	 */
	public function getQueueTransport()
	{
		if ($this->queue_transport !== null) return $this->queue_transport;

		$db_proc = new DatabaseQueueProcessor();
		$this->queue_transport = new QueueTransport($db_proc, $this->event_dispatcher);

		return $this->queue_transport;
	}


	/**
	 * @param \Swift_Mime_Message $message
	 * @param null $failedRecipients
	 * @return int
	 */
	public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
	{
		if ($message instanceof Message) {
			$message->prepare();
		}

		if ($evt = $this->event_dispatcher->createSendEvent($this, $message)) {
			$this->event_dispatcher->dispatchEvent($evt, 'beforeSendPerformed');
			if ($evt->bubbleCancelled()) {
				return 0;
			}
		}

		$queue_pref = App::getSetting('core.use_mail_queue');
		$use_queue = false;
		if ($queue_pref == 'always' OR ($queue_pref == 'smart' AND $message->isQueueHinted())) {
			$use_queue = true;
		}

		if ($use_queue && $this->isQueueEnabled()) {
			$use_queue = false;
		}

		$is_retrying = false;
		if ($message instanceof \Application\DeskPRO\Mail\Message) {
			$is_retrying = $message->getIsRetrying();
		}

		if ($is_retrying && $use_queue) {
			$use_queue = false;
		}

		$bcc_list = App::getSetting('core.bcc_all_emails');
		if ($bcc_list) {
			foreach (explode(',',$bcc_list) as $bcc_e) {
				$message->addBcc(trim($bcc_e));
			}
		}

		$success = false;

		if ($message->getSpecificTransport()) {
			$tr = $message->getSpecificTransport();
			if (!$tr->isStarted()) $tr->start();

			$success = $tr->send($message, $failedRecipients);
		} elseif ($use_queue) {
			$tr= $this->getQueueTransport();
			if (!$tr->isStarted()) $tr->start();

			$success = $tr->send($message);
		} else {
			try {
				$tr = $this->getTransportForMessage($message);
				if (!$tr->isStarted()) $tr->start();

				$success = $tr->send($message, $failedRecipients);
			} catch (\Swift_TransportException $e) {
				$backup_tr = $this->getTransportForMessage($message, true);

				if ($backup_tr) {
					if (!$backup_tr->isStarted()) $backup_tr->start();
					$success = $backup_tr->send($message, $failedRecipients);
				}
			}

			if (!$success) {
				if (!$is_retrying && $this->isQueueEnabled()) {
					$success = $this->getQueueTransport()->send($message);
				} else {
					$success = false;
				}
			} else {
				// Save a logged copy too
				$queue_proc = $this->getQueueTransport()->getQueueProcessor();
				if (!$is_retrying && $queue_proc instanceof DatabaseQueueProcessor) {
					$queue_proc->addLoggedMessage($message);
				}
			}
		}

		if ($evt) {
			$evt->setResult($success ? \Swift_Events_SendEvent::RESULT_SUCCESS : \Swift_Events_SendEvent::RESULT_FAILED);
			$this->event_dispatcher->dispatchEvent($evt, 'sendPerformed');
		}

		return $success;
	}


	/**
	 * Given a message, inspect the 'From' address to see which transport we sholud use to send it.
	 *
	 * @param \Swift_Mime_Message $message
	 * @param bool $get_backup_transport
	 * @return \Swift_MailTransport
	 */
	public function getTransportForMessage(\Swift_Mime_Message $message, $get_backup_transport = false)
	{
		$from_address_model = $message->getFrom();
		$from_address = array_keys($from_address_model);

		if (!$from_address) $from_address = '';
		else $from_address = $from_address[0];


		$from_account = App::getEntityRepository('DeskPRO:EmailTransport')->findTransportForAddress($from_address);
		if ($from_account) {
			if ($get_backup_transport) {
				$tr = null;
			} else {
				$tr = $from_account->getTransport();
			}
		} else {
			try {
				App::logErrorMessage('mail_send', 'WARN', "No account found to send from {$from_address}", array('raw_message' => $message->toString()));
			} catch (\Exception $e) {}

			$tr = new \Swift_MailTransport();
		}

		return $tr;
	}


	public function registerPlugin(\Swift_Events_EventListener $plugin)
	{
		$this->event_dispatcher->bindEventListener($plugin);
	}

	public function isStarted()
	{
		return true;
	}

	public function start()
	{

	}

	public function stop()
	{

	}
}
