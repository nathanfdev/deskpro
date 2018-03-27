<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SwiftMailer;

use Application\EmailBundle\SwiftMailer\Message\MessageFactoryInterface;
use Application\EmailBundle\SwiftMailer\Transport\StorageTransportInterface;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swift_Mime_Message;

class Mailer extends \Swift_Mailer implements StorageTransportInterface
{
    /**
     * @var MessageFactoryInterface
     */
    private $message_factory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * @param \Swift_Transport        $transport
     * @param MessageFactoryInterface $message_factory
     * @param LoggerInterface         $logger
     */
    public function __construct(\Swift_Transport $transport, MessageFactoryInterface $message_factory, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();

        $tmpdir = dp_get_tmp_dir().'/swiftmailer-cache';
        if (!is_dir(dp_get_tmp_dir().'/swiftmailer-cache')) {
            if (!@mkdir($tmpdir, 0777, true)) {
                $tmpdir = sys_get_temp_dir().'/dp-swiftmailer-cache';
                if (!is_dir($tmpdir)) {
                    @mkdir($tmpdir, 0777, true);
                }
            }
        }

        if (!is_dir($tmpdir) || !is_writable($tmpdir)) {
            // Fall back on system tmp dir
            $tmpdir = sys_get_temp_dir();
        }

        \Swift_Preferences::getInstance()->setTempDir($tmpdir);
        $GLOBALS['DP_SWIFTMAIL_TMPDIR'] = $tmpdir;

        parent::__construct($transport);

        $this->message_factory = $message_factory;
    }

    /**
     * @param Swift_Mime_Message $message
     */
    private function preprocessMessage(Swift_Mime_Message $message)
    {
        $ref = Numbers::roundToMultiple(time(), 5).'-'.Strings::random(40, Strings::CHARS_ALPHANUM_IU);
        $message->getHeaders()->addTextHeader('X-DeskPRO-MessageRef', $ref);

        try {
            $pubref = \DpSys\License::getLicense()->getPublicLicenseRef();
            $message->getHeaders()->addTextHeader('X-DP-LREF', $pubref);
        } catch (\Exception $e) {
        }

        $this->logger->debug(sprintf('Preprocessing: %s', $message->getId()));

        if ($message instanceof \Orb\Mail\Message) {
            $t = microtime(true);
            $message->prepare();
            $this->logger->debug(sprintf('Orb prepare took %.3fs', microtime(true) - $t));
        }
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
        $tr = $this->getTransport();

        if (!($tr instanceof StorageTransportInterface)) {
            throw new \BadMethodCallException('Transport does not support queueing');
        }

        $this->preprocessMessage($message);

        return $tr->queueMessage($message, $send_date);
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
        $tr = $this->getTransport();

        if (!($tr instanceof StorageTransportInterface)) {
            throw new \BadMethodCallException('Transport does not support queueing');
        }

        $this->preprocessMessage($message);

        return $tr->insertMessage($message);
    }

    /**
     * @param Swift_Mime_Message $message
     * @param null               $failedRecipients
     *
     * @return int
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->preprocessMessage($message);

        return parent::send($message, $failedRecipients);
    }

    /**
     * @param string $service
     *
     * @return \Application\EmailBundle\SwiftMailer\Message\Message
     */
    public function createMessage($service = 'message')
    {
        $message = $this->message_factory->createMessage($service);

        if (!$message) {
            $message = parent::createMessage($service);
        }

        return $message;
    }
}
