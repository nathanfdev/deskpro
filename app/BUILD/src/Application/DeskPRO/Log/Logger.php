<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Log;

class Logger extends \Orb\Log\Logger
{
    /**
     * Name of this log "file" for those writers that support it.
     *
     * @var string
     */
    protected $_log_name = null;

    /**
     * Set the log name.
     *
     * @param string $log_name
     */
    public function setLogName($log_name)
    {
        $this->_log_name = $log_name;
    }

    /**
     * @param array $info
     *
     * @return LogItem
     */
    public function createLogInfoObject(array $info)
    {
        if (!isset($info[LogItem::LOG_NAME]) && $this->_log_name) {
            $info[LogItem::LOG_NAME] = $this->_log_name;
        }

        $log_item = new LogItem($info);

        return $log_item;
    }
}
