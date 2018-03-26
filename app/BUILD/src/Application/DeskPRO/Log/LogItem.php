<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Log;

class LogItem extends \Orb\Log\LogItem
{
    const LOG_NAME = 'log_name';
    const FLAG     = 'flag';

    protected function init()
    {
        $this->_standard_fields[] = self::LOG_NAME;
        $this->_standard_fields[] = self::FLAG;

        if (!isset($this[self::LOG_NAME])) {
            $this[self::LOG_NAME] = 'general';
        }
        if (!isset($this[self::FLAG])) {
            $this[self::FLAG] = null;
        }
    }

    public function getLogName()
    {
        return $this[self::LOG_NAME];
    }

    public function getFlag()
    {
        return $this[self::FLAG];
    }
}
