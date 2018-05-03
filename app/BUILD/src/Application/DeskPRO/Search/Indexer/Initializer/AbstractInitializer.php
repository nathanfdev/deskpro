<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Search\Indexer\Initializer;

use Application\DeskPRO\Search\Adapter\AbstractAdapter;
use Orb\Log\Logger;

/**
 * An initializer goes through and resets an index, and indexes all existing content.
 */
abstract class AbstractInitializer
{
    /**
     * @var \Application\DeskPRO\Search\Adapter\AbstractAdapter
     */
    protected $adapter;

    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    public function __construct(AbstractAdapter $adapter, Logger $logger = null)
    {
        $this->adapter = $adapter;

        if (!$this->logger) {
            $this->logger = new Logger();
        } else {
            $this->logger = $logger;
        }
    }
}
