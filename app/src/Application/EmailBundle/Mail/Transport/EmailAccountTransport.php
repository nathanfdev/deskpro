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

use Orb\Util\Arrays;
use Swift_Transport;
use Swift_Events_EventDispatcher;
use Swift_Mime_Message;
    use Swift_Events_SendEvent;
use Swift_Events_EventListener;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;

class EmailAccountTransport implements \Swift_Transport
{
    /**
     * @var Swift_Events_EventDispatcher
     */
    private $event_dispatcher;

    /**
     * @var Swift_Transport[]
     */
    private $started_transports = array();

    /**
     * @var string
     */
    private $last_log = "";

    /**
     * @param EmailAccountManager           $email_accounts
     * @param Swift_Events_EventDispatcher $event_dispatcher
     */
    public function __construct(EmailAccountManager $email_accounts, Swift_Events_EventDispatcher $event_dispatcher)
    {
        $this->event_dispatcher = $event_dispatcher;
        $this->email_accounts   = $email_accounts;

        if (!empty($GLOBALS['DP_CONFIG']['debug']['mail']['disable_send'])) {
            $this->construct_log[] = sprintf('[%s] Mailer: disable_send = enabled', date('Y-m-d H:i:s'));
            $this->registerPlugin(new \Orb\Mail\Plugins\CancelSend());
        }
    }

    /**
     * Gets log for last sent message.
     *
     * @return string
     */
    public function getLastLog()
    {
        return $this->last_log;
    }

    /**
     * Tests if this Transport mechanism has started.
     *
     * @return boolean
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Starts this Transport mechanism.
     */
    public function start()
    {
    }

    /**
     * Stops this Transport mechanism.
     */
    public function stop()
    {
        foreach ($this->started_transports as $tr) {
            try {
                $tr->stop();
            } catch (\Exception $e) {}
        }
    }

    /**
     * Sends the given message.
     *
     * @param Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return integer The number of sent emails
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->last_log = '';
        try {
            $ret = $this->_send($message, $failedRecipients);
        } catch (\Exception $e) {
            $d = date('Y-m-d H:i:s');
            $this->last_log .= trim($this->last_log) . "\n[$d] Exception: " . $e->getMessage();
            throw $e;
        }

        return $ret;
    }

    private function _send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        // Re-applying From account because the one saved in DeskproTransport might
        // not be valid anymore
        $acc = $this->email_accounts->findAccountForSwiftmailerMessage($message);
        $from_name = Arrays::getFirstItem($message->getFrom() ?: array()) ?: '';
        $message->setFrom($acc->getUseEmailAddress(), $from_name);

        $d = date('Y-m-d H:i:s');
        $this->last_log .= "[$d] EmailAccountTransport: Sending message " . $message->getId() . "\n";
        $this->last_log .= "[$d] EmailAccountTransport: Sending from " . $acc->getUseEmailAddress() . " -- Account " . $acc->id . "\n";

        if ($acc->id) {
            if (isset($this->started_transports[$acc->id])) {
                $tr = $this->started_transports[$acc->id];
            } else {
                $tr = $this->email_accounts->getTransportForAccount($acc);
                $tr->start();
                $this->started_transports[$acc->id] = $tr;
            }
        } else {
            $tr = $this->email_accounts->getTransportForAccount($acc);
        }

        if ($evt = $this->event_dispatcher->createSendEvent($tr, $message)) {
            $this->event_dispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                $this->last_log .= "[$d] EmailAccountTransport: Send cancelled via beforeSendPerformed";
                return 0;
            }
        }

        $sent = $tr->send($message, $failedRecipients);

        if ($evt) {
            $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
            $this->event_dispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        if (isset($tr->__dp_logger)) {
            $this->last_log .= $tr->__dp_logger->getMessageLogs();
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
}