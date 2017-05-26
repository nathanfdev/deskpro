<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

/**
 * Parser / recognizer for various identifiers
 */
class IdentifierParser
{
    /**
     * @param string $raw
     * @return bool
     */
    public function recognizeApplicationName($raw)
    {
        //TODO: implement application name recognition
        return false === (bool) preg_match('#^\d+$#', $raw);
    }

    /**
     * @param string $raw
     * @return bool
     */
    public function recognizeApplicationInstanceId($raw)
    {
        //TODO: implement application instance id recognition
        return (bool) preg_match('#^\d+$#', $raw);
    }
}
