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

namespace Application\ImportBundle\ValueImporter;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\ImportBundle\RecordMapper\RecordMapperRegistry;
use Application\DeskPRO\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractValueImporter
 * @package Application\ImportBundle\ValueImporter
 */
abstract class AbstractValueImporter implements ValueImporterInterface
{
    const MODE_LIVE = 'live';
    const MODE_TEST = 'test';

    /**
     * @var string
     */
    private $mode;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    /**
     * @var \Application\ImportBundle\RecordMapper\RecordMapperRegistry
     */
    private $mappers;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var DeskPROContainer $container
     */
    private $container;

    /**
     * Constructor
     *
     * @param string               $mode
     * @param DeskproContainer     $container
     * @param LoggerInterface      $logger
     * @param RecordMapperRegistry $mappers
     */
    public function __construct($mode, DeskproContainer $container, LoggerInterface $logger, RecordMapperRegistry $mappers)
    {
        $this->mode      = $mode;
        $this->container = $container;
        $this->db        = $container->getDb();
        $this->logger    = $logger;
        $this->mappers   = $mappers;
    }

    /**
     * @return DeskproContainer
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @return bool
     */
    protected function isTestMode()
    {
        return $this->mode == self::MODE_TEST;
    }

    /**
     * @return string
     */
    protected function getMode()
    {
        return $this->mode;
    }

    /**
     * @return Connection
     */
    protected function getDb()
    {
        return $this->db;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return RecordMapperRegistry
     */
    protected function getMappers()
    {
        return $this->mappers;
    }
}
