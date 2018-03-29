<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SwiftMailer\Plugins;

use Psr\Log\LoggerInterface;
use Swift_Events_SendEvent;

class TransportLogger implements \Swift_Events_CommandListener, \Swift_Events_ResponseListener, \Swift_Events_TransportChangeListener, \Swift_Events_TransportExceptionListener, \Swift_Events_SendListener
{
    /**
     * @var array
     */
    private $connection_log = [];

    /**
     * @var array
     */
    private $message_logs = [];

    /**
     * @var bool
     */
    private $is_connected = false;

    /**
     * @var int
     */
    private $message_count = 0;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $disable_connection_log = false;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Prevents the connection log (which might include auth info)
     * from being saved.
     */
    public function disableConnectionLog()
    {
        $this->disable_connection_log = true;
    }

    /**
     * @param string $message
     */
    public function addConnectionLog($message)
    {
        if ($this->disable_connection_log) {
            if (!$this->connection_log) {
                $message = '<< connection >>';
                $this->logger->debug($message);
                $this->connection_log[] = '['.date('Y-m-d H:i:s').'] '.trim($message);
            }
        } else {
            $this->logger->debug($message);
            $this->connection_log[] = '['.date('Y-m-d H:i:s').'] '.trim($message);
        }
    }

    /**
     * @param string $message
     */
    public function addMessageLog($message)
    {
        $this->logger->debug($message);
        $this->message_logs[] = '['.date('Y-m-d H:i:s').'] '.trim($message);
    }

    /**
     * @param bool $with_connection
     *
     * @return string
     */
    public function getMessageLogs($with_connection = true)
    {
        $l = implode("\n", $this->message_logs);

        if ($with_connection) {
            $l = implode("\n", $this->connection_log)."\n".$l;
        }

        return $l;
    }

    /**
     * @param bool $with_connection
     *
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
     * Clears message logs.
     */
    public function resetMessageLogs()
    {
        $this->message_logs = [];
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
            $this->addMessageLog(sprintf('>> %s', $command));
        } else {
            $this->addConnectionLog(sprintf('>> %s', $command));
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
            $this->addMessageLog(sprintf('<< %s', $response));
        } else {
            $this->addConnectionLog(sprintf('<< %s', $response));
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
        $this->addConnectionLog(sprintf('++ Starting %s', $transportName));
    }

    /**
     * Invoked immediately after the Transport is started.
     *
     * @param \Swift_Events_TransportChangeEvent $evt
     */
    public function transportStarted(\Swift_Events_TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->addConnectionLog(sprintf('++ %s started', $transportName));
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
        $this->message_logs = [];
        ++$this->message_count;

        // Prepend connection log so the log for a single message is 'complete'
        if ($this->connection_log) {
            $this->addMessageLog(sprintf('%d messages were sent before this. Here is the connection log from the initial connection:', $this->message_count - 1));
            foreach ($this->connection_log as $l) {
                $this->addMessageLog('<Connect History> '.$l);
            }
        }
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
        $e       = $evt->getException();
        $message = $e->getMessage();

        if ($this->is_connected) {
            $this->addMessageLog(sprintf('!! %s', $message));
        } else {
            $this->addConnectionLog(sprintf('!! %s', $message));
        }

        $message .= PHP_EOL;
        $evt->cancelBubble();
        throw new \Swift_TransportException($message);
    }
}
