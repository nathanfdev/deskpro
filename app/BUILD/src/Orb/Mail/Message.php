<?php

/**
 * Orb.
 */

namespace Orb\Mail;

/**
 * Represents an email message to send.
 */
class Message extends \Swift_Message
{
    /**
     * @var bool
     */
    protected $_suppress_autoreply = false;

    /**
     * @var bool
     */
    protected $has_prepared = false;

    /**
     * @var bool
     */
    protected $has_presend = false;

    /**
     * Metadata that might be used by the transports or queue processor.
     *
     * @var array
     */
    public $meta = [];

    public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
    {
        call_user_func_array([$this, 'Swift_Mime_SimpleMessage::__construct'], \Swift_DependencyContainer::getInstance()->createDependenciesFor('mime.message'));

        if (!isset($charset)) {
            $charset = \Swift_DependencyContainer::getInstance()->lookup('properties.charset');
        }

        $this->setSubject($subject);
        $this->setBody($body);
        $this->setCharset($charset);
        if ($contentType) {
            $this->setContentType($contentType);
        }
    }

    /**
     * Prepares the message to be set. This is a hook that is called right before sending.
     */
    public function prepare()
    {
        if ($this->has_prepared) {
            return;
        }

        $this->has_prepared = true;

        $this->preSend();
        $this->doPrepare();
    }

    public function doPrepare()
    {
    }

    /**
     * Called just before a send attempt.
     */
    public function preSend()
    {
        if (!$this->has_presend) {
            $from = $this->getFrom();
            if ($from && count($from) == 1) {
                if (!$this->getReplyTo()) {
                    $this->setReplyTo($from);
                }

                if (!$this->_suppress_autoreply && !$this->getReturnPath()) {
                    $addr = array_keys($from);
                    $addr = array_pop($addr);
                    $this->setReturnPath($addr);
                }
            }

            if ($this->_suppress_autoreply) {
                // Tell Outlook/Exchange to suppress autoreplies (http://msdn.microsoft.com/en-us/library/ee219609(v=exchg.80).aspx)
                $this->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'All');
            }

            $this->doPreSend(false);
        } else {
            $this->doPreSend(true);
        }

        $this->has_presend = true;
    }

    protected function doPreSend($is_retry = false)
    {
    }

    /**
     * Set the suppress autoreplies headers.
     *
     * @param bool $on
     */
    public function setSuppressAutoreplies($on = true)
    {
        $this->_suppress_autoreply = (bool) $on;
    }

    /**
     * @static
     *
     * @param null $subject
     * @param null $body
     * @param null $contentType
     * @param null $charset
     *
     * @return \Orb\Mail\Message
     */
    public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
    {
        return new self($subject, $body, $contentType, $charset);
    }
}
