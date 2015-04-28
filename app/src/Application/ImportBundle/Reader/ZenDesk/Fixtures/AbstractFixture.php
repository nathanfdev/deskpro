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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures;

use Psr\Log\LoggerInterface;
use Zendesk\API\Client;
use DateTime;
use Zendesk\API\ResponseException;

/**
 * ZenDesk abstract fixture
 *
 * Class AbstractFixture
 * @package Application\ImportBundle\Reader\ZenDesk\Fixtures
 */
abstract class AbstractFixture implements FixtureInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create($offset, DateTime $initial_time, DateTime $end_time)
    {
        for ($i = $offset; $i < $offset + self::COUNT; $i++) {
            try {
                $this->logInfo(sprintf('Importing fixture `%s_%d`', $this->getEntityType(), $i));
                $this->createItem($i, $initial_time, $end_time);

                $this->logInfo(sprintf('Fixture `%s_%d` imported successfully', $this->getEntityType(), $i));

            } catch (ResponseException $e) {
                $this->logWarning(sprintf(
                    'Unable to create %s, code `%s`, headers:',

                    $this->getEntityType(),
                    $this->client->getDebug()->lastResponseCode
                ));

                $this->logWarning($this->client->getDebug()->lastRequestHeaders);
            }
        }
    }

    /**
     * Create a fixture item
     *
     * @param int      $prefix
     * @param DateTime $initial_time
     * @param DateTime $end_time
     *
     * @return void
     */
    protected abstract function createItem($prefix, DateTime $initial_time, DateTime $end_time);

    /**
     * Log info message if logger is defined
     *
     * @param string $message
     */
    protected function logInfo($message)
    {
        if ($this->logger) {
            $this->logger->info($message);
        }
    }

    /**
     * Log warning message if logger is defined
     *
     * @param string $message
     */
    protected function logWarning($message)
    {
        if ($this->logger) {
            $this->logger->warning($message);
        }
    }
}
