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
 * @subpackage EmailBundle
 */

namespace Application\EmailBundle\Mail\Transport;

use Application\DeskPRO\BlobStorage;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\EmailBundle\Mail\SourceMapper\SourceMapperInterface;
use Orb\Util\Arrays;
use Swift_Transport;
use Swift_Events_EventDispatcher;
use Swift_Mime_Message;
use Swift_Events_SendEvent;
use Swift_Events_EventListener;

class DeskproTransport implements Swift_Transport
{
    /**
     * @var Swift_Events_EventDispatcher
     */
    private $event_dispatcher;

    /**
     * @var EmailAccountManager
     */
    private $email_accounts;

    /**
     * @var Swift_Transport
     */
    private $transport;

    /**
     * @var SourceMapperInterface
     */
    private $source_mapper;

    /**
     * @var bool
     */
    private $queue_mode = true;

    /**
     * @var string
     */
    private $last_log = '';

    /**
     * @param SourceMapperInterface $source_mapper
     * @param EmailAccountManager $email_accounts
     * @param Swift_Transport $real_transport
     * @param Swift_Events_EventDispatcher $event_dispatcher
     */
    public function __construct(SourceMapperInterface $source_mapper, EmailAccountManager $email_accounts, Swift_Transport $real_transport, Swift_Events_EventDispatcher $event_dispatcher)
    {
        $this->transport        = $real_transport;
        $this->event_dispatcher = $event_dispatcher;
        $this->email_accounts   = $email_accounts;
        $this->source_mapper    = $source_mapper;

        if (!empty($GLOBALS['DP_CONFIG']['mail_queue_mode'])) {
            $s = $GLOBALS['DP_CONFIG']['mail_queue_mode'];
            if ($s === false || $s == 0 || $s == 'off' || $s == 'disable' || $s == 'disabled') {
                $this->setQueueMode(false);
            }
        }
    }


    /**
     * @param Swift_Mime_Message $message
     */
    private function preprocessMessage(Swift_Mime_Message $message)
    {
        if (isset($message->__dp_deskpro_transport_done_preproc)) {
            return;
        }

        $acc = $this->email_accounts->findAccountForSwiftmailerMessage($message);
        $from_name = Arrays::getFirstItem($message->getFrom() ?: array()) ?: '';
        $message->setFrom($acc->getUseEmailAddress(), $from_name);

        $message->__dp_deskpro_transport_done_preproc = true;
    }


    /**
     * Set the queue mode (on/off)
     *
     * @param string $mode
     */
    public function setQueueMode($mode)
    {
        $this->queue_mode = $mode;
    }


    /**
     * Tests if this Transport mechanism has started.
     *
     * @return boolean
     */
    public function isStarted()
    {
        return $this->transport->isStarted();
    }


    /**
     * Starts this Transport mechanism.
     */
    public function start()
    {
        return $this->transport->start();
    }


    /**
     * Stops this Transport mechanism.
     */
    public function stop()
    {
        return $this->transport->stop();
    }


    /**
     * @return string
     */
    public function getLastLog()
    {
        if ($this->transport instanceof EmailAccountTransport) {
            return trim($this->last_log . "\n" . $this->transport->getLastLog());
        }

        return sprintf("%s does not support getLastLog", get_class($this->transport));
    }


    /**
     * Queue the message so it is sent by the queue processor.
     *
     * @param Swift_Mime_Message $message
     * @param \DateTime $send_date When to send the message. If not specified, it will be sent the next time the processor is run.
     * @return int
     */
    public function queueMessage(Swift_Mime_Message $message, \DateTime $send_date = null)
    {
        $this->last_log = '';

        $this->preprocessMessage($message);
        $r = $this->source_mapper->createSourceForMessage($message, 'pending', $send_date);

        $this->last_log = sprintf('[%s] DeskproTransport: Message %d queued as pending -- %s', date('Y-m-d H:i:s'), $r['id'], $r['ref']);

        return $r['id'];
    }


    /**
     * Save the message to the DB.
     *
     * @param Swift_Mime_Message $message
     * @return int
     */
    public function insertMessage(Swift_Mime_Message $message)
    {
        $this->last_log = '';

        $this->preprocessMessage($message);
        $r = $this->getOrCreateSource($message, 'inserted');

        $this->last_log = sprintf('[%s] DeskproTransport: Message %d queued as inserted -- %s', date('Y-m-d H:i:s'), $r['id'], $r['ref']);

        return $r['id'];
    }


    /**
     * Sends the given message. Disables any queue that might be enabled.
     *
     * @param Swift_Mime_Message $message
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return integer The number of sent emails
     */
    public function sendNow(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->last_log = '';
        $this->preprocessMessage($message);

        $r = $this->source_mapper->createSourceForMessage($message, 'processing');

        if ($evt = $this->event_dispatcher->createSendEvent($this, $message)) {
            $this->event_dispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                $this->last_log = sprintf('[%s] DeskproTransport: Aborted because beforeSendPerformed cancelled message', date('Y-m-d H:i:s'));
                $this->source_mapper->markSourceAborted($r, sprintf('[%s] Aborted because beforeSendPerformed cancelled message', date('Y-m-d H:i:s')));
                return 0;
            }
        }

        try {
            $sent = $this->transport->send($message, $failedRecipients);

            if ($sent) {
                $this->source_mapper->markSourceComplete($r, $this->getLastLog());
            } else {
                $this->source_mapper->markSourceError($r, 'no_send', $this->getLastLog());
            }
        } catch (\Exception $e) {
            $this->source_mapper->markSourceRetry($r, $this->getLastLog() . "\n" . sprintf("[%s] Exception: %s %s", date('Y-m-d H:i:d'), $e->getCode(), $e->getMessage()));
            return 0;
        }

        return $sent;
    }


    /**
     * Sends the given message. This might queue the message if queueing is enabled.
     *
     * @param Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return integer The number of sent emails
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->last_log = '';
        $this->preprocessMessage($message);

        if ($evt = $this->event_dispatcher->createSendEvent($this, $message)) {
            $this->event_dispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                $this->source_mapper->createSourceForMessage($message, 'aborted');
                return 0;
            }
        }

        $do_queue = false;
        if ($this->queue_mode == 'enabled' || $this->queue_mode === true || $this->queue_mode == 1) {
            $do_queue = true;
        }

        if ($do_queue) {
            $this->queueMessage($message);
            $sent = 1;
        } else {
            $sent = $this->sendNow($message, $failedRecipients);
        }

        if ($evt) {
            $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
            $this->event_dispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        return $sent;
    }


    /**
     * Register a plugin.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->event_dispatcher->bindEventListener($plugin);
    }


    /**
     * @return Swift_Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }
}