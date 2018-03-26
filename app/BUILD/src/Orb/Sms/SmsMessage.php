<?php

/**
 * Orb.
 */

namespace Orb\Sms;

use Orb\Util\Strings;

/**
 * Represents a single message that needs to be sent. SmsMessages are one or many chunks of 160 characters.
 * Each SmsMessageChunk holds a SmsResult of it's current status.
 *
 * A value object.
 */
class SmsMessage
{
    const STATUS_SUCCESS = 'success';
    const STATUS_PENDING = 'queued';
    const STATUS_FAILED  = 'failed';

    /**
     * @var string full message, without chunking
     */
    private $rawMessage;

    /**
     * @var SmsMessageChunk[]|array the split string
     */
    private $chunks;

    public function __construct($rawMessage)
    {
        $this->rawMessage = $rawMessage;
        $chunks           = Strings::splitStringIntoArray($rawMessage, 160);
        $this->chunks     = [];
        foreach ($chunks as $chunk) {
            $this->chunks[] = new SmsMessageChunk($chunk);
        }
    }

    /**
     * This method will advance over time to allow for queuing, pending, etc.
     *
     * @return bool
     */
    public function isSent()
    {
        $status = self::STATUS_SUCCESS;

        foreach ($this->chunks as $chunk) {
            if (!$chunk->isSent()) {
                $status = self::STATUS_FAILED;
            }
        }

        return $status == self::STATUS_SUCCESS;
    }

    /**
     * @return SmsMessageChunk[]
     */
    public function getChunks()
    {
        return $this->chunks;
    }

    /**
     * @return bool
     */
    public function hasMultipleChunks()
    {
        return count($this->chunks) > 1;
    }

    /**
     * @return string
     */
    public function getRawMessage()
    {
        return $this->rawMessage;
    }
}
