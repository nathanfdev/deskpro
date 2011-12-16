<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Mail
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Mail\Transport;

use Application\DeskPRO\App;

use Orb\Mail\Message;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * This transport takes care of initializing any other transports based on settings
 * etc, and also queuing.
 */
class DelegatingTransport implements \Swift_Transport
{
	protected $queue_disabled = false;

	protected $event_dispatcher;
	protected $transports = array();
	protected $queue_transport = null;

	public function __construct(\Swift_Events_EventDispatcher $event_dispatcher)
	{
		$this->event_dispatcher = $event_dispatcher;
	}

	public function disableQueue()
	{
		$this->queue_disabled = true;
	}

	public function enableQueue()
	{
		$this->queue_disabled = false;
	}

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

		$db_proc = new \Application\DeskPRO\Mail\QueueProcessor\Database();
		$this->queue_transport = new \Orb\Mail\Transport\QueueTransport($db_proc, $this->event_dispatcher);

		return $this->queue_transport;
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

	public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
	{
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

		$bcc_list = App::getSetting('core.bcc_all_emails');
		if ($bcc_list) {
			foreach (explode(',',$bcc_list) as $bcc_e) {
				$message->addBcc(trim($bcc_e));
			}
		}

		if ($message->getSpecificTransport()) {
			$tr = $message->getSpecificTransport();
			$tr->send($message, $failedRecipients);
		} elseif ($use_queue AND $this->isQueueEnabled()) {
			$success = $this->getQueueTransport()->send($message);
		} else {
			try {
				$success = $this->getTransportForMessage($message)->send($message, $failedRecipients);
			} catch (\Swift_TransportException $e) {
				$backup_tr = $this->getTransportForMessage($message, true);
				if ($backup_tr) {
					$success = $backup_tr->send($message, $failedRecipients);
				}
			}

			if (!$success AND $this->isQueueEnabled()) {
				$success = $this->getQueueTransport()->send($message);
			}
		}

		if ($evt) {
			$evt->setResult($success ? \Swift_Events_SendEvent::RESULT_SUCCESS : \Swift_Events_SendEvent::RESULT_FAILED);
			$this->event_dispatcher->dispatchEvent($evt, 'sendPerformed');
		}

		return $success;
	}

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
}
