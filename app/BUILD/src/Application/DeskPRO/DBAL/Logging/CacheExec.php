<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DBAL\Logging;

use Application\DeskPRO\CacheInvalidator\QueryListener;

class CacheExec extends \Symfony\Bridge\Doctrine\Logger\DbalLogger
{
    /**
     * @var \Application\DeskPRO\CacheInvalidator\QueryListener
     */
    protected $query_listener;

    /**
     * @var array
     */
    protected $last_query;

    public function __construct(QueryListener $query_listener)
    {
        $this->query_listener = $query_listener;
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        if ($params === null) {
            $params = [];
        }
        $this->last_query = [$sql, $params];
    }

    public function stopQuery()
    {
        if (!$this->last_query) {
            return;
        }

        $this->query_listener->handleQuery($this->last_query[0], $this->last_query[1]);

        $this->last_query = null;
    }
}
