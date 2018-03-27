<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Monolog\Handler;

use Monolog\Handler\AbstractHandler;
use Orb\Log\Logger as OrbLogger;

class OrbLoggerAdapterHandler extends AbstractHandler
{
    /**
     * @var \Orb\Log\Logger
     */
    private $orb_logger;

    /**
     * @var array
     */
    private static $pri_map = [
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARN',
        400 => 'ERR',
        500 => 'CRIT',
        550 => 'ALERT',
        600 => 'EMERG',
    ];

    public function __construct(OrbLogger $logger)
    {
        $this->orb_logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        if (!$this->orb_logger->isEnabled()) {
            return false;
        }

        return true;
    }

    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        if (!isset(self::$pri_map[$record['level']])) {
            $pri = 'NOTICE';
        } else {
            $pri = self::$pri_map[$record['level']];
        }

        $this->orb_logger->log($record['message'], $pri);

        return false;
    }
}
