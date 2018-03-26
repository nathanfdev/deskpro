<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

/**
 * Parser / recognizer for various identifiers.
 */
class IdentifierParser
{
    /**
     * @param string $raw
     *
     * @return ApplicationRef|null
     */
    public function parseApplicationRef($raw)
    {
        if ($this->recognizeNumericIdentifier($raw)) {
            return new ApplicationRef($raw, false);
        }

        $appId = $this->parseApplicationId($raw);
        if (!is_null($appId)) {
            return new ApplicationRef($appId, false);
        }

        //let's consider it an application name
        return new ApplicationRef($raw, true);
    }

    /**
     * @param $raw
     *
     * @return string|null
     */
    public function parseApplicationId($raw)
    {
        if (1 === preg_match('#^app:(\d+)$#', $raw, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param string $raw
     *
     * @return bool
     */
    public function recognizeNumericIdentifier($raw)
    {
        //TODO: implement application instance id recognition
        return (bool) preg_match('#^(\d+)$#', $raw);
    }
}
