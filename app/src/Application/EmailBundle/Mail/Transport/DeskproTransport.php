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

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\BlobStorage;
use Application\DeskPRO\Mail\Message as DeskproMessage;
use Application\EmailBundle\Entity\SendmailSource;
use Swift_Transport;
use Swift_Events_EventDispatcher;
use Swift_Message;
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
     * @var Swift_Transport
     */
    private $transport;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var DeskproBlobStorage
     */
    private $blob_storage;

    /**
     * @var string
     */
    private $queue_mode = 'never';

    /**
     * @param Connection                   $db
     * @param DeskproBlobStorage           $blob_storage
     * @param Swift_Transport              $real_transport
     * @param Swift_Events_EventDispatcher $event_dispatcher
     */
    public function __construct(Connection $db, DeskproBlobStorage $blob_storage, Swift_Transport $real_transport, Swift_Events_EventDispatcher $event_dispatcher)
    {
        $this->transport        = $real_transport;
        $this->event_dispatcher = $event_dispatcher;
        $this->db               = $db;
        $this->blob_storage     = $blob_storage;
        $this->message_factory  = $message_factory;
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
     * @return int
     */
    public function queueMessage(Swift_Mime_Message $message)
    {
        $r = $this->saveMessage($message, 'pending');
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
        $r = $this->saveMessage($message, 'inserted');
        return $r['id'];
    }


    /**
     * Sends the given message. Disables any queue that might be enabled.
     *
     * @param Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return integer The number of sent emails
     */
    public function sendNow(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $r = $this->saveMessage($message, 'processing');

        $sent = $this->transport->send($message, $failedRecipients);

        $update = array();
        if ($message instanceof Swift_Message) {
            if ($message->getHeaders()->get('X-DeskPRO-EmailAccountId')) {
                $update['email_account_id'] = $message->getHeaders()->get('X-DeskPRO-EmailAccountId')->getFieldBody();
            }
        }

        $update['exec_count'] = $r['exec_count'] + 1;
        if ($sent) {
            $this->updateMessageStatus($r, 'complete', 'complete', $update);
        } else {
            $this->updateMessageStatus($r, 'error', 'error', $update);
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
        if ($evt = $this->event_dispatcher->createSendEvent($this, $message)) {
            $this->event_dispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                $this->saveMessage($message, 'aborted');
                return 0;
            }
        }

        $do_queue = false;
        if ($this->queue_mode == 'always') {
            $do_queue = true;
        } else if ($this->queue_mode == 'smart') {
            if ($message instanceof DeskproMessage) {
                if ($message->isQueueHinted()) {
                    $do_queue = true;
                }
            }
        }

        if ($do_queue) {
            $this->queue($message);
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
     * @param Swift_Mime_Message $message
     * @param string             $as
     * @param bool               $ignore_existing
     * @return array
     */
    private function saveMessage(Swift_Mime_Message $message, $as = 'inserted', $ignore_existing = false)
    {
        if ($message instanceof DeskproMessage) {
            $message->prepare();
        }

        if (!$ignore_existing && $message instanceof Swift_Message) {
            $exist_id = $message->getHeaders()->get('X-DeskPRO-SendmailSourceId')->getFieldBody();
            if ($exist_id) {
                $row = $this->db->fetchAssoc("SELECT * FROM sendmail_sources WHERE id = ?", array($exist_id));
                if ($row) {
                    if ($row['status'] != $as) {
                        $update = array('status' => $as);
                        switch ($as) {
                            case SendmailSource::STATUS_PENDING:
                            case SendmailSource::STATUS_RETRY:
                                $update['date_next_attempt'] = date('Y-m-d H:i:s');
                                break;
                            default:
                                $update['date_next_attempt'] = null;
                        }

                        $this->db->update('sendmail_sources', $update, array('id' => $row['id']));
                        $row = array_merge($row, $update);
                    }

                    return $row;
                }
            }
        }

        $blob = $this->blob_storage->createBlobRowFromString($message->toString(), 'out_email.eml', 'message/rfc822');

        $header_to_raw  = $message->getTo();
        $header_to      = array();
        if ($header_to_raw) {
            foreach ($header_to_raw as $email => $name) {
                if ($name) {
                    $header_to[] = "$name <$email>";
                } else {
                    $header_to[] = $email;
                }
            }
        }
        $header_to = implode(', ', $header_to);

        $header_subject_raw = $message->getHeaders()->get('Subject');
        $header_subject = '';
        if ($header_subject_raw) {
            $header_subject = $header_subject_raw;
        }

        $header_from_raw = $message->getHeaders()->get('From');
        $header_from = array();
        if ($header_from_raw) {
            foreach ($header_from_raw as $email => $name) {
                if ($name) {
                    $header_from[] = "$name <$email>";
                } else {
                    $header_from[] = $email;
                }
            }
        }
        $header_from = implode(', ', $header_from);

        $date = date('Y-m-d H:i:s');

        $record = array(
            'blob_id'        => $blob['id'],
            'headers'        => $message->getHeaders()->toString(),
            'header_to'      => $header_to,
            'header_from'    => $header_from,
            'header_subject' => $header_subject,
            'status'         => $as,
            'date_status'    => $date,
            'date_created'   => $date,
            'exec_count'     => 0
        );

        if ($this->transport instanceof EmailAccountTransport) {
            $acc = $this->transport->getAccountForMessage($message);
            if ($acc && $acc->id) {
                $record['email_account_id'] = $acc->id;
            }
        }

        if ($as == 'pending') {
            $record['date_next_attempt'] = $date;
        }

        $this->db->insert('sendmail_sources', $record);
        $record['id'] = $this->db->lastInsertId();

        if ($message instanceof Swift_Message) {
            if ($ignore_existing) {
                $message->getHeaders()->removeAll('X-DeskPRO-SendmailSourceId');
            }
            $message->getHeaders()->addTextHeader('X-DeskPRO-SendmailSourceId', $record['id']);
        }

        return $record;
    }


    /**
     * @param array|int $row
     * @param string    $status
     * @param string    $log
     * @param array     $update
     * @return array
     * @throws \Exception
     */
    private function updateMessageStatus($row, $status, $log, array $update = array())
    {
        if (!is_array($row)) {
            $row = $this->db->fetchAssoc("SELECT * FROM sendmail_sources WHERE id = ?", array($row));
        }

        if (!$row || !is_array($row)) {
            throw new \InvalidArgumentException();
        }

        $log_text = '';
        $old_log_blob = null;
        if ($row['log_blob_id']) {
            $old_log_blob = $this->db->fetchAssoc("SELECT * FROM blobs WHERE id = ?", array($row['log_blob_id']));
            if ($old_log_blob) {
                try {
                    $rec = $this->blob_storage->getBlobFromBlobRow($old_log_blob);
                    $log_text = $this->blob_storage->copyBlobToString($rec, $old_log_blob['storage_loc']);
                } catch (\Exception $e) {}
            }
        }

        if ($log_text) {
            $log_text .= "\n\n\n" . $log;
        }

        $new_log_blob = $this->blob_storage->createBlobRowFromString($log_text, 'log.txt', 'text/plain');
        $update['status'] = $status;
        $update['log_blob_id'] = $new_log_blob['id'];
        switch ($status) {
            case SendmailSource::STATUS_PENDING:
            case SendmailSource::STATUS_RETRY:
                if (!isset($update['date_next_attempt'])) {
                    $update['date_next_attempt'] = date('Y-m-d H:i:s');
                }
                break;
            default:
                $update['date_next_attempt'] = null;
        }

        $this->db->update('sendmail_sources', $update, array('id' => $row['id']));
        $row = array_merge($row, $update);

        if ($old_log_blob) {
            $this->blob_storage->deleteBlobRow($old_log_blob);
        }

        return $row;
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