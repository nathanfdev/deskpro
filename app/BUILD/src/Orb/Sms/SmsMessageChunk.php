<?php

/**
 * Orb.
 */

namespace Orb\Sms;

/**
 * Represents a single text message of 160 characters or less.
 */
class SmsMessageChunk
{
    /**
     * @var string text message
     */
    private $text;

    /**
     * @var SmsResult|null null until a send attempt, then it knows the last SmsResult
     */
    private $result;

    public function __construct($text, SmsResult $result = null)
    {
        if (strlen($text) > 160) {
            throw new SmsException(
                "SMS text message chunks cannot be greater than 160 characters. Given '$text'"
            );
        }

        $this->text   = $text;
        $this->result = $result;
    }

    public function __toString()
    {
        return $this->text ?: '';
    }

    /**
     * @return bool
     */
    public function isSent()
    {
        if (!$this->result) {
            return false;
        }

        return $this->result->isSent();
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return SmsResult
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param null|SmsResult $result
     */
    public function setResult(SmsResult $result = null)
    {
        $this->result = $result;
    }
}
