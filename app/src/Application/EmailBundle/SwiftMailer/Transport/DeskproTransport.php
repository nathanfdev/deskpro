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

namespace Application\EmailBundle\SwiftMailer\Transport;

use Application\DeskPRO\BlobStorage;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\EmailBundle\SwiftMailer\SourceMapper\SourceMapperInterface;
use Orb\Util\Arrays;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swift_Transport;
use Swift_Events_EventDispatcher;
use Swift_Mime_Message;
use Swift_Events_SendEvent;

class DeskproTransport implements Swift_Transport, StorageTransportInterface
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
     * @var SourceMapperInterface
     */
    private $source_mapper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * @param SourceMapperInterface $source_mapper
     * @param EmailAccountManager $email_accounts
     * @param Swift_Events_EventDispatcher $event_dispatcher
     * @param LoggerInterface $logger
     */
    public function __construct(SourceMapperInterface $source_mapper, EmailAccountManager $email_accounts, Swift_Events_EventDispatcher $event_dispatcher, LoggerInterface $logger = null)
    {
        $this->event_dispatcher = $event_dispatcher;
        $this->email_accounts   = $email_accounts;
        $this->source_mapper    = $source_mapper;
        $this->logger           = $logger ?: new NullLogger();
    }


    /**
     * @param Swift_Mime_Message $message
     */
    private function preprocessMessage(Swift_Mime_Message $message)
    {
        if (isset($message->__dp_deskpro_transport_done_preproc)) {
            return;
        }

        if ($from = Arrays::kvpairs($message->getFrom())) {
            $this->logger->debug(sprintf("[Before processing] From: Name = %s, Email = <%s>", $from[1], $from[0]));
        } else {
            $this->logger->debug("[Before processing] From is empty");
        }

        $acc = $this->email_accounts->findAccountForSwiftmailerMessage($message);
        $from_name = Arrays::getFirstItem($message->getFrom() ?: array()) ?: '';
        $message->setFrom($acc->getUseEmailAddress(), $from_name);

        if ($acc) {
            $this->logger->debug(sprintf("Detected account #%d <%s>", $acc->id, $acc->address));
        }

        if ($from = Arrays::kvpairs($message->getFrom())) {
            $this->logger->debug(sprintf("From: Name = %s, Email = <%s>", $from[1], $from[0]));
        } else {
            $this->logger->debug("From is empty");
        }

        $message->__dp_deskpro_transport_done_preproc = true;
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
     * Queue the message so it is sent by the queue processor.
     *
     * @param Swift_Mime_Message $message
     * @param \DateTime $send_date When to send the message. If not specified, it will be sent the next time the processor is run.
     * @return int
     */
    public function queueMessage(Swift_Mime_Message $message, \DateTime $send_date = null)
    {
        $this->preprocessMessage($message);
        $r = $this->source_mapper->createSourceForMessage($message, 'pending', $send_date);

        $this->logger->info(sprintf('[%s] Message %d queued as pending -- %s', $message->getId(), $r['id'], $r['ref']), array('mail_message' => $message));

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
        $this->preprocessMessage($message);
        $r = $this->getOrCreateSource($message, 'inserted');

        $this->logger->info(sprintf('[%s] Message %d queued as inserted -- %s', $message->getId(), $r['id'], $r['ref']), array('mail_message' => $message));

        return $r['id'];
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
        $this->preprocessMessage($message);

        if ($evt = $this->event_dispatcher->createSendEvent($this, $message)) {
            $this->event_dispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                $r = $this->source_mapper->createSourceForMessage($message, 'aborted');
                $this->logger->info(sprintf('[%s] Message %d aborted -- %s', $message->getId(), $r['id'], $r['ref']), array('mail_message' => $message));
                return 0;
            }
        }

        $this->queueMessage($message);
        $sent = 1;

        if ($evt) {
            $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
            $this->event_dispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        return $sent;
    }


    /**
     * Register a plugin.
     *
     * @param \Swift_Events_EventListener $plugin
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->event_dispatcher->bindEventListener($plugin);
    }
}