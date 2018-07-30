<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SwiftMailer\Transport;

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\EmailBundle\SourceMapper\SourceMapperInterface;
use Application\EmailBundle\SwiftMailer\Message\MessageOptionsInterface;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Orb\Util\Arrays;
use Orb\Validator\StringEmail;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swift_Events_EventDispatcher;
use Swift_Events_SendEvent;
use Swift_Mime_Message;
use Swift_Transport;

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
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * @param SourceMapperInterface        $source_mapper
     * @param EmailAccountManager          $email_accounts
     * @param Swift_Events_EventDispatcher $event_dispatcher
     * @param LoggerInterface              $logger
     * @param BrandStack                   $brandStack
     * @param SettingsResolver             $settingsResolver
     */
    public function __construct(
        SourceMapperInterface $source_mapper,
        EmailAccountManager $email_accounts,
        Swift_Events_EventDispatcher $event_dispatcher,
        BrandStack $brandStack,
        SettingsResolver $settingsResolver,
        LoggerInterface $logger = null
    ) {
        $this->event_dispatcher = $event_dispatcher;
        $this->email_accounts   = $email_accounts;
        $this->source_mapper    = $source_mapper;
        $this->brandStack       = $brandStack;
        $this->settingsResolver = $settingsResolver;
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
            $from = $from[0];
            $this->logger->debug(sprintf('[Before processing] From: Name = %s, Email = <%s>', $from[1], $from[0]));
        } else {
            $this->logger->debug('[Before processing] From is empty');
        }

        $brand     = $this->brandStack->getActive()->getBrand();
        $acc       = $this->email_accounts->findAccountForSwiftmailerMessage($message, $brand);
        $from_name = Arrays::getFirstItem($message->getFrom() ?: [])
            ?: $this->settingsResolver->getBrandSettings($brand->getId())->get('core.deskpro_name');
        $from_email = $acc->getUseEmailAddress();

        if ($message instanceof MessageOptionsInterface) {
            if ($message->getMessageOptions()->has(MessageOptionsInterface::OPT_USE_FROM)) {
                $from_email = $message->getMessageOptions()->get(MessageOptionsInterface::OPT_USE_FROM);
            }
        }

        $message->setFrom($from_email, $from_name);

        if ($acc) {
            $this->logger->debug(sprintf('Detected account #%d <%s>', $acc->id, $acc->address));
        }

        if ($from = Arrays::kvpairs($message->getFrom())) {
            $from = $from[0];
            $this->logger->debug(sprintf('From: Name = %s, Email = <%s>', $from[1], $from[0]));
        } else {
            $this->logger->debug('From is empty');
        }

        $message->__dp_deskpro_transport_done_preproc = true;
    }

    /**
     * Tests if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
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
    }

    /**
     * Queue the message so it is sent by the queue processor.
     *
     * @param Swift_Mime_Message $message
     * @param \DateTime          $send_date When to send the message. If not specified, it will be sent the next time the processor is run
     *
     * @return int
     */
    public function queueMessage(Swift_Mime_Message $message, \DateTime $send_date = null)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $this->preprocessMessage($message);

        if ($DP_ENV->getConfig('settings.disable_outgoing_email')) {
            $r = $this->source_mapper->createSourceForMessage($message, 'aborted');
            $this->logger->info(sprintf('Message %d queued as aborted (disable_send is enabled in config)', $r['id']), ['sendmail_source_id' => $r['id']]);
            $r = $this->source_mapper->setLogText($r);
        } elseif (!$this->_validateNewMessage($message, $error_message)) {
            $r = $this->source_mapper->createSourceForMessage($message, 'aborted');
            $this->logger->info(sprintf('Message %d queued as aborted (failed validation) -- '.$error_message, $r['id']), ['sendmail_source_id' => $r['id']]);
            $r = $this->source_mapper->setLogText($r);
        } else {
            $r = $this->source_mapper->createSourceForMessage($message, 'pending', $send_date);
            if ($r['status'] === 'pending') {
                $this->logger->info(sprintf('Message %d queued as pending', $r['id']), ['sendmail_source_id' => $r['id']]);
            } else {
                $this->logger->info(sprintf('Message %d queued as %s %s', $r['id'], $r['status'], @$r['error_code']), ['sendmail_source_id' => $r['id']]);
            }
            $r = $this->source_mapper->setLogText($r);
        }

        return $r['id'];
    }

    /**
     * Before queueing a message, it validates it to see if we should send it. If this returns
     * false, the message will be saved as aborted.
     *
     * @param Swift_Mime_Message $message
     * @param null               $error_message
     *
     * @return bool
     */
    private function _validateNewMessage(Swift_Mime_Message $message, &$error_message = null)
    {
        $tos        = $message->getTo();
        $is_invalid = false;

        foreach ($tos as $addy => $name) {
            if (StringEmail::isExampleEmail($addy)) {
                $is_invalid = $addy;
                break;
            }
        }

        if ($is_invalid !== false) {
            $error_message = "$is_invalid is an invalid email address.";

            return false;
        }

        return true;
    }

    /**
     * Save the message to the DB.
     *
     * @param Swift_Mime_Message $message
     *
     * @return int
     */
    public function insertMessage(Swift_Mime_Message $message)
    {
        $this->preprocessMessage($message);

        $r = $this->source_mapper->createSourceForMessage($message, 'inserted');
        $this->logger->info(sprintf('Message %d queued as inserted', $r['id']), ['sendmail_source_id' => $r['ref']]);
        $r = $this->source_mapper->setLogText($r);

        return $r['id'];
    }

    /**
     * Sends the given message. This might queue the message if queueing is enabled.
     *
     * @param Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return int The number of sent emails
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->preprocessMessage($message);

        if ($evt = $this->event_dispatcher->createSendEvent($this, $message)) {
            $this->event_dispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                $r = $this->source_mapper->createSourceForMessage($message, 'aborted');
                $this->logger->info(sprintf('Message %d aborted', $r['id']), ['sendmail_source_id' => $r['ref']]);
                $r = $this->source_mapper->setLogText($r);

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
