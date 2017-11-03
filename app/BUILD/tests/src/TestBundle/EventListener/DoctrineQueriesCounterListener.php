<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DpTestSrc\TestBundle\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class DoctrineQueriesCounterListener.
 */
class DoctrineQueriesCounterListener
{
    const MAX_QUERIES_COUNT = 100;
    const MAX_FETCH_ROWS    = 1000;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int
     */
    private static $maxQueriesCount = self::MAX_QUERIES_COUNT;

    /**
     * @var int
     */
    private static $maxFetchRows = self::MAX_FETCH_ROWS;

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int $maxQueriesCount
     */
    public function setMaxQueriesCount($maxQueriesCount)
    {
        self::$maxQueriesCount = $maxQueriesCount;
    }

    /**
     * @param int $maxFetchRows
     */
    public function setMaxFetchRows($maxFetchRows)
    {
        self::$maxFetchRows = $maxFetchRows;
    }

    public function resetSettings()
    {
        self::$maxQueriesCount = self::MAX_QUERIES_COUNT;
        self::$maxFetchRows    = self::MAX_FETCH_ROWS;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        // $controller passed can be either a class or a Closure.
        // This is not usual in Symfony but it may happen.
        // If it is a class, it comes in array format
        if (!is_array($controller)) {
            return;
        }

        $this->connection->getConfiguration()->setSQLLogger(new DebugStack());
    }

    public function onKernelResponse()
    {
        $logger = $this->connection->getConfiguration()->getSQLLogger();
        if (!$logger instanceof DebugStack) {
            return;
        }

        $this->connection->getConfiguration()->setSQLLogger(null);

        $queries = [];
        foreach ($logger->queries as $query) {
            if (preg_match('/SELECT (?!COUNT\()/i', $query['sql'])) {
                $queries[] = $query;
            }
        }

        // check max queries count
        if (count($queries) > self::$maxQueriesCount) {
            throw new \Exception(sprintf(
                'Too many db queries, expected less than %d, got %d',
                self::$maxQueriesCount, count($queries)
            ));
        }

        // run EXPLAIN on all SELECT queries
        foreach ($queries as $query) {
            $sql       = $query['sql'];
            $statement = $this->connection->executeQuery('EXPLAIN '.$sql, $query['params'] ?: [], $query['types'] ?: []);
            $result    = $statement->fetchAll();

            foreach ($result as $subQuery) {
                if ($subQuery['rows'] > self::$maxFetchRows) {
                    throw new \Exception(sprintf(
                        'Too many rows fetched, expected less than %d, got %d. Sql: %s',
                        self::$maxFetchRows, $subQuery['rows'], $sql
                    ));
                }
                if ($subQuery['rows'] > 100 && $subQuery['select_type'] !== 'DERIVED' && $subQuery['type'] === 'ALL') {
                    throw new \Exception(sprintf('Sub query of type ALL detected. Sql: %s', $sql));
                }
            }
        }
    }
}
