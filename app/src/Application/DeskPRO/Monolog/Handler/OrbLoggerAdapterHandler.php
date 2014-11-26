<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
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
    private static $pri_map = array(
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARN',
        400 => 'ERR',
        500 => 'CRIT',
        550 => 'ALERT',
        600 => 'EMERG',
    );

    public function __construct(OrbLogger $logger)
    {
        $this->orb_logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(array $record)
    {
        if (!$this->orb_logger->isEnabled()) {
            return false;
        }
        if (!count($this->orb_logger->getWriterChain())) {
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
