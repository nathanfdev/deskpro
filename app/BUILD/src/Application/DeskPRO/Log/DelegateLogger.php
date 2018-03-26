<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Log;

class DelegateLogger extends Logger
{
    /**
     * @var \Orb\Log\Logger
     */
    private $logger;

    /**
     * DelegateLogger constructor.
     *
     * @param $logger
     */
    public function __construct(\Orb\Log\Logger $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    public function logItem(\Orb\Log\LogItem $log_item)
    {
        $log_item = $this->_writer_chain->filterLogItem($log_item);
        if (!$log_item) {
            return;
        }
        $this->_writer_chain->write($log_item);
        $this->logger->logItem($log_item);
    }
}
