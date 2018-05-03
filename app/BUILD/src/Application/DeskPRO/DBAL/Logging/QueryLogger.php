<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DBAL\Logging;

use Application\DeskPRO\App;
use Orb\Log\Logger;

/**
 * Log various query information.
 */
class QueryLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
    const TYPE_SELECT = 1;
    const TYPE_UPDATE = 2;
    const TYPE_INSERT = 4;
    const TYPE_DELETE = 8;
    const TYPE_OTHER  = 16;
    const TYPE_ALL    = 63;

    /**
     * @var \Orb\Log\Logger
     */
    protected $_logger = null;

    /**
     * @var bool
     */
    protected $_is_enabled = true;
    /**
     * @var array
     */
    protected $_slowlog_rules = [];

    /**
     * @var array
     */
    protected $_queries = [];
    /**
     * @var int
     */
    protected $_last_query = -1;

    /**
     * @var int
     */
    protected $_query_counter = 0;
    /**
     * @var float
     */
    protected $_query_total_time = 0.0;

    /**
     * @var bool
     */
    protected $disable_trace = true;

    /**
     * @var bool
     */
    protected $keep_queries = false;

    /**
     * @var int
     */
    public $query_count = 0;
    /**
     * @var float
     */
    public $total_time = 0.0;
    /**
     * @var string
     */
    public $tag = '';

    public $ignore_triggers     = [];
    public $ignored_query_start = null;

    /**
     * True when logging a query to the log. We need this incase the logger
     * itself is logging to the database, we dont want to log the log of the log log.
     *
     * @var bool
     */
    protected $_is_logging = false;

    public function startQuery($sql, array $params = null, array $types = null)
    {
        if ($this->_is_logging) {
            return;
        }
        if (!$this->_is_enabled) {
            return;
        }

        if ($this->ignore_triggers) {
            foreach ($this->ignore_triggers as $p) {
                if (strpos($sql, $p) !== false) {
                    $this->ignored_query_start = microtime(true);

                    return;
                }
            }
        }

        $sql = trim($sql);
        if (preg_match('#^\s*SELECT#i', $sql)) {
            $query_type     = self::TYPE_SELECT;
            $query_typename = 'SELECT';
        } elseif (preg_match('#^\s*UPDATE#i', $sql)) {
            $query_type     = self::TYPE_UPDATE;
            $query_typename = 'UPDATE';
        } elseif (preg_match('#^\s*INSERT#i', $sql)) {
            $query_type     = self::TYPE_INSERT;
            $query_typename = 'INSERT';
        } elseif (preg_match('#^\s*DELETE#i', $sql)) {
            $query_type     = self::TYPE_DELETE;
            $query_typename = 'DELETE';
        } else {
            $query_type     = self::TYPE_OTHER;
            $query_typename = 'OTHER';
        }

        ++$this->_last_query;

        $trans_level = App::getDb()->getTransactionNestingLevel();

        $this->_queries[$this->_last_query] = [
            'trans_level'    => $trans_level,
            'tag'            => $this->tag,
            'sql'            => $sql,
            'params'         => $params,
            'types'          => $types,
            'query_type'     => $query_type,
            'query_typename' => $query_typename,
            'time_start'     => microtime(true),
            'time_end'       => 0,
            'time_taken'     => 0,
        ];
    }

    public function stopQuery()
    {
        if ($this->_is_logging) {
            return;
        }
        if ($this->ignored_query_start) {
            $timetaken = microtime(true) - $this->ignored_query_start;
            ++$this->query_count;
            $this->total_time += $timetaken;

            ++$this->_query_counter;
            $this->_query_total_time += $timetaken;

            $this->ignored_query_start = false;

            return;
        }
        if (!$this->_is_enabled or $this->_last_query == -1) {
            return;
        }

        $queryinfo               = &$this->_queries[$this->_last_query];
        $queryinfo['time_end']   = microtime(true);
        $queryinfo['time_taken'] = $queryinfo['time_end'] - $queryinfo['time_start'];

        ++$this->query_count;
        $this->total_time += $queryinfo['time_taken'];

        ++$this->_query_counter;
        $this->_query_total_time += $queryinfo['time_taken'];

        $this->_is_logging = true;

        $trace = false;
        $table = false;
        foreach ($this->_slowlog_rules as $rule) {
            if (($queryinfo['query_type'] == self::TYPE_ALL || $queryinfo['query_type'] & $rule[0]) and $queryinfo['time_taken'] >= $rule[1]) {
                if (!$trace && !$this->disable_trace) {
                    try {
                        throw new \Exception();
                    } catch (\Exception $e) {
                        $trace = str_replace(DP_ROOT, '', $e->getTraceAsString());
                    }
                }
                if (!$table) {
                    if (preg_match('#\s+(FROM|INSERT INTO|UPDATE|DELETE FROM)\s+(.*?)\s+#', $queryinfo['sql'], $m)) {
                        $table = $m[2];
                    } else {
                        $table = $queryinfo['sql'];
                    }
                }

                $queryinfo['table'] = $table;
                $queryinfo['trace'] = $trace;

                $this->getLogger()->log("SQL log against $table", Logger::NOTICE, ['queryinfo' => $queryinfo]);
                break;
            }
        }

        if (!$this->keep_queries) {
            $this->_queries    = [];
            $this->_last_query = -1;
        }

        $this->_is_logging = false;
    }

    /**
     * Get all query info we've logged.
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->_queries;
    }

    /**
     * Is this logger currently enabled?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_is_enabled;
    }

    /**
     * Enable this logger.
     */
    public function enable()
    {
        $this->_is_enabled = true;
    }

    /**
     * Disable this logger.
     */
    public function disable()
    {
        $this->_is_enabled = false;
    }

    /**
     * Add a slow logging rule.
     *
     * @param <type> $query_type
     * @param <type> $max_time
     */
    public function addSlowLogRule($query_type, $max_time)
    {
        $this->_slowlog_rules[] = [$query_type, $max_time];
    }

    /**
     * @return float
     */
    public function getTotalTime()
    {
        return $this->total_time;
    }

    /**
     * Get the slow log rules currently set.
     *
     * @return array
     */
    public function getSlowLogRules()
    {
        return $this->_slowlog_rules;
    }

    /**
     * Set a specific array of slowlog rules. Rules must be an array of array(type, maxtime).
     *
     * @param array $rules
     */
    public function setSlowLogRules(array $slowlog_rules)
    {
        $this->_slowlog_rules = $slowlog_rules;
    }

    /**
     * Set the logger.
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Get the assigned logger.
     *
     * @return Logger
     */
    public function getLogger()
    {
        if (!$this->_logger) {
            $this->_logger = App::createNewLogger('query-log', microtime(true));
        }

        return $this->_logger;
    }
}
