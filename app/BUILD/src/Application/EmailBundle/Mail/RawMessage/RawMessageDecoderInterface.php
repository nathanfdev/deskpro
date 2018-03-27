<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawMessage;

interface RawMessageDecoderInterface
{
    /**
     * @param resource $raw_fp
     *
     * @return RawMessage
     */
    public function createRawMessage($raw_fp);
}
