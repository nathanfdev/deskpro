<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

class IdentifierParser
{
    /**
     * @param string $raw
     * @return bool
     */
    public function recognizeApplicationName($raw)
    {
        return false;
    }

    /**
     * @param string $raw
     * @return bool
     */
    public function recognizeApplicationInstanceId($raw)
    {
        return (bool) preg_match('#^\d+$#', $raw);
    }
}
