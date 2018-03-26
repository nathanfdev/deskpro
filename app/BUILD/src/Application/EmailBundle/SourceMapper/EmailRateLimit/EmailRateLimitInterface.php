<?php

namespace Application\EmailBundle\SourceMapper\EmailRateLimit;

interface EmailRateLimitInterface
{
    /**
     * @param array $message The message array (as returned by the SourceMapper)
     *
     * @return bool
     */
    public function isLimited(array $message);
}
