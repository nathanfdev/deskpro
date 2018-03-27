<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper\EmailRateLimit;

class NullEmailRateLimit implements EmailRateLimitInterface
{
    /**
     * {@@inheritdoc}.
     */
    public function isLimited(array $message)
    {
        return false;
    }
}
