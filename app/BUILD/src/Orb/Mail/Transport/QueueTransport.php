<?php

/**
 * Orb.
 */

namespace Orb\Mail\Transport;

use Application\DeskPRO\App;

/**
 * Queue mail transport.
 */
class QueueTransport implements \Swift_Transport
{
    /** @var \Swift_Events_EventDispatcher */
    protected $_event_dispatcher;

    public function __construct(\Swift_Events_EventDispatcher $event_dispatcher)
    {
        $this->_event_dispatcher = $event_dispatcher;
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
        if ($evt = $this->_event_dispatcher->createSendEvent($this, $message)) {
            $this->_event_dispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        $log = '';
        if ($message instanceof \Application\DeskPRO\Mail\Message) {
            $log = $message->getLogMessages();
            $message->clearLogMessages();
        }

        $blob = App::getContainer()->getBlobStorage()->createBlobRowFromString(serialize($message), 'sendmail.obj', 'plain/text');

        $sendmail = [
            'date_created'      => date('Y-m-d H:i:s'),
            'blob_id'           => $blob['id'],
            'subject'           => $message->getSubject() ?: '(No Subject)',
            'date_next_attempt' => date('Y-m-d H:i:s'),
            'priority'          => 10,
            'log'               => '',
            'status'            => 'pending',
        ];

        if ($message instanceof \Orb\Mail\Message) {
            $sendmail['priority'] = $message->getQueuePriority();
        }

        $tos = [];
        foreach ($message->getTo() as $addr => $name) {
            $tos[] = $addr;
        }
        $sendmail['to_address'] = implode(',', $tos);

        foreach ($message->getFrom() as $addr => $name) {
            $sendmail['from_address'] = $addr;
        }

        if ($log) {
            $sendmail['log'] = $log;
        }

        App::getDb()->insert('sendmail_queue', $sendmail);

        if ($evt) {
            $evt->setResult(\Swift_Events_SendEvent::RESULT_SUCCESS);
            $this->_event_dispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        return 1;
    }

    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->_event_dispatcher->bindEventListener($plugin);
    }
}
