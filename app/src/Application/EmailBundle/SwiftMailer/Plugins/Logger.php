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
 * @subpackage
 */

namespace Application\EmailBundle\SwiftMailer\Plugins;

use Swift_Events_SendEvent;

class Logger implements \Swift_Events_CommandListener, \Swift_Events_ResponseListener, \Swift_Events_TransportChangeListener, \Swift_Events_TransportExceptionListener, \Swift_Events_SendListener
{
    /**
     * @var array
     */
    private $connection_log = array();

    /**
     * @var array
     */
    private $message_logs = array();

    /**
     * @var bool
     */
    private $is_connected = false;

    /**
     * @param string $message
     */
    public function addConnectionLog($message)
    {
        $this->connection_log[] = '[' . date('Y-m-d H:i:s') . '] ' . trim($message);
    }

    /**
     * @param string $message
     */
    public function addMessageLog($message)
    {
        $this->message_logs[] = '[' . date('Y-m-d H:i:s') . '] ' . trim($message);
    }

    /**
     * @param bool $with_connection
     * @return string
     */
    public function getMessageLogs($with_connection = true)
    {
        $l = implode("\n", $this->message_logs);

        if ($with_connection) {
            $l = implode("\n", $this->connection_log) . "\n" . $l;
        }

        return $l;
    }

    /**
     * @param bool $with_connection
     * @return array
     */
    public function getMessageLogsAsArray($with_connection = true)
    {
        if ($with_connection) {
            return array_merge($this->connection_log, $this->message_logs);
        } else {
            return $this->message_logs;
        }
    }


    /**
     * Clears message logs
     */
    public function resetMessageLogs()
    {
        $this->message_logs = array();
    }



    /**
     * Invoked immediately following a command being sent.
     *
     * @param \Swift_Events_CommandEvent $evt
     */
    public function commandSent(\Swift_Events_CommandEvent $evt)
    {
        $command = $evt->getCommand();

        if ($this->is_connected) {
            $this->addMessageLog(sprintf(">> %s", $command));
        } else {
            $this->addConnectionLog(sprintf(">> %s", $command));
        }
    }

    /**
     * Invoked immediately following a response coming back.
     *
     * @param \Swift_Events_ResponseEvent $evt
     */
    public function responseReceived(\Swift_Events_ResponseEvent $evt)
    {
        $response = $evt->getResponse();
        if ($this->is_connected) {
            $this->addMessageLog(sprintf("<< %s", $response));
        } else {
            $this->addConnectionLog(sprintf("<< %s", $response));
        }
    }

    /**
     * Invoked just before a Transport is started.
     *
     * @param \Swift_Events_TransportChangeEvent $evt
     */
    public function beforeTransportStarted(\Swift_Events_TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->addConnectionLog(sprintf("++ Starting %s", $transportName));
    }

    /**
     * Invoked immediately after the Transport is started.
     *
     * @param \Swift_Events_TransportChangeEvent $evt
     */
    public function transportStarted(\Swift_Events_TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->addConnectionLog(sprintf("++ %s started", $transportName));
        $this->is_connected = true;
    }

    /**
     * Invoked just before a Transport is stopped.
     *
     * @param \Swift_Events_TransportChangeEvent $evt
     */
    public function beforeTransportStopped(\Swift_Events_TransportChangeEvent $evt)
    {
        //$transportName = get_class($evt->getSource());
        //sprintf("++ Stopping %s", $transportName));
    }

    /**
     * Invoked immediately after the Transport is stopped.
     *
     * @param \Swift_Events_TransportChangeEvent $evt
     */
    public function transportStopped(\Swift_Events_TransportChangeEvent $evt)
    {
        //$transportName = get_class($evt->getSource());
        //sprintf("++ %s stopped", $transportName));
        $this->is_connected = false;
    }

    /**
     * Invoked immediately before the Message is sent.
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        // Reset message logger
        $this->message_logs = array();
    }

    /**
     * Invoked immediately after the Message is sent.
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {

    }

    /**
     * Invoked as a TransportException is thrown in the Transport system.
     *
     * @param \Swift_Events_TransportExceptionEvent $evt
     */
    public function exceptionThrown(\Swift_Events_TransportExceptionEvent $evt)
    {
        $e = $evt->getException();
        $message = $e->getMessage();

        if ($this->is_connected) {
            $this->addMessageLog(sprintf("!! %s", $message));
        } else {
            $this->addConnectionLog(sprintf("!! %s", $message));
        }

        $message .= PHP_EOL;
        $evt->cancelBubble();
        throw new \Swift_TransportException($message);
    }
}