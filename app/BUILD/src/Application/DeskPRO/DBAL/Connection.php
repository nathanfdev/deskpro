<?php

/**
 * DeskPRO.
 *
 * @category Controller
 */

namespace Application\DeskPRO\DBAL;

use Doctrine\DBAL\DBALException;
use DpSys\LowError\SystemErrorHandler;
use PDO;

/**
 * Some enhancements to Doctrine's connection class.
 */
class Connection extends \Doctrine\DBAL\Connection
{
    const EVENT_POST_COMMIT   = 'onPostCommit';
    const EVENT_POST_ROLLBACK = 'onPostRollback';

    /**
     * @var int
     */
    protected $_max_packet_size = null;

    /**
     * @var \Orb\Log\Logger
     */
    protected $transaction_logger = false;

    /**
     * @var bool
     */
    protected $running_trans_event = false;

    /**
     * @var string
     */
    protected $names_charset = 'UTF8';

    /**
     * @var array
     */
    protected $trans_ids = [];

    /**
     * @var int
     */
    protected $trans_count = 0;

    /**
     * @var array
     */
    protected $writes_in_tx = [];

    /**
     * @var bool
     */
    protected $has_run_avoid = false;

    /**
     * @var string
     */
    protected $default_isolation = 'REPEATABLE READ';

    /**
     * @var bool
     */
    protected $do_reset_isolation = false;

    /**
     * @var int
     */
    private $connectAttempts = 0;

    public function connect()
    {
        ++$this->connectAttempts;

        try {
            return $this->doConnect();
        } catch (\Exception $e) {
            $params = $this->getParams();

            // It can be common to have a bit of a network glitch that prevents a connection
            // from failing, and its better to retry once now then show the user a failure screen

            if (isset($params['dp_connect_attempts']) && $this->connectAttempts < $params['dp_connect_attempts']) {
                usleep(500000); // half a second
                return $this->doConnect();
            }

            throw $e;
        }

        if ($this->connectAttempts === 1) {
            \DpShutdown::add(function (Connection $db) {
                if ($db->isTransactionActive()) {
                    $e = new \RuntimeException('WARNING: Unclosed transaction at shutdown');
                    SystemErrorHandler::logException($e, false, 'unclosed_trans_shutdown');
                    try {
                        while ($db->isTransactionActive()) {
                            $db->commit();
                        }
                    } catch (\Exception $e) {
                    }
                }
            }, [$db], null, 1000);
        }
    }

    private function doConnect()
    {
        if (parent::connect()) {
            $this->exec("SET sql_mode='', time_zone='+00:00'");

            if ($this->names_charset) {
                $this->exec("SET NAMES '{$this->names_charset}'");
            }

            return true;
        }

        return false;
    }

    /**
     * Modifies the wait_timeout and "pings" the MySQL server to keep the connection alive.
     */
    public function avoidTimeout()
    {
        if (!$this->has_run_avoid) {
            try {
                $this->exec('SET SESSION wait_timeout = 1800');
            } catch (\Exception $e) {
            }
            $this->has_run_avoid = true;
        }

        try {
            $this->fetchColumn('SELECT 1');
        } catch (\Exception $e) {
        }
    }

    /**
     * Gets the max packet size.
     *
     * @return int
     */
    public function getMaxPacketSize()
    {
        if ($this->_max_packet_size !== null) {
            return $this->_max_packet_size;
        }

        $result                 = $this->fetchAssoc("SHOW variables LIKE 'max_allowed_packet'");
        $this->_max_packet_size = $result['Value'];

        return $this->_max_packet_size;
    }

    /**
     * Execute a query and return all results indexed with the specified column.
     *
     * @param string $statement
     * @param array  $params
     * @param string $index
     *
     * @return array
     */
    public function fetchAllKeyed($statement, array $params = [], $index = 'id', $types = [])
    {
        $statement = $this->executeQuery($statement, $params, $types);
        $array     = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $array[$row[$index]] = $row;
        }

        return $array;
    }

    /**
     * Execute a query and return all results grouped into a multi-dimentional array by $group_key.
     * Optionally, the sub-array can be indexed by $index_key.
     *
     * @param $statement
     * @param array $params
     * @param $group_key
     * @param null  $index_key
     * @param null  $col_key
     * @param array $types
     *
     * @return array
     */
    public function fetchAllGrouped($statement, array $params, $group_key, $index_key = null, $col_key = null, $types = [])
    {
        $statement = $this->executeQuery($statement, $params, $types);
        $array     = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if (!isset($array[$row[$group_key]])) {
                $array[$row[$group_key]] = [];
            }

            $val = $row;
            if ($col_key !== null) {
                $val = $row[$col_key];
            }

            if ($index_key !== null) {
                $array[$row[$group_key]][$row[$index_key]] = $val;
            } else {
                $array[$row[$group_key]][] = $val;
            }
        }

        return $array;
    }

    /**
     * Execute a query and return a key=>value pair.
     *
     * @param $statement
     * @param array $params
     * @param array $types
     * @param int   $key_index
     * @param int   $val_index
     * @param int   $mode      Change to PDO::FETCH_ASSOC if you want to specify a string indexes
     * @param int   $nullkey
     *
     * @return array
     */
    public function fetchAllKeyValue($statement, array $params = [], $types = [], $key_index = 0, $val_index = 1, $mode = PDO::FETCH_NUM, $nullkey = 0)
    {
        $statement = $this->executeQuery($statement, $params, $types);
        $array     = [];

        while ($row = $statement->fetch($mode)) {
            if ($row[$key_index] === null) {
                $row[$key_index] = $nullkey;
            }

            $array[$row[$key_index]] = $row[$val_index];
        }

        return $array;
    }

    /**
     * Execute a query and return an array of all values from one column.
     *
     * @param string $statement
     * @param array  $params
     * @param string $index
     * @param int    $mode      Change to PDO::FETCH_ASSOC if you want to specify a string $index
     *
     * @return array
     */
    public function fetchAllCol($statement, array $params = [], $types = [], $index = 0, $mode = PDO::FETCH_NUM)
    {
        $statement = $this->executeQuery($statement, $params, $types);
        $array     = [];

        while ($row = $statement->fetch($mode)) {
            $array[] = $row[$index];
        }

        return $array;
    }

    /**
     * Builds SQL for multiple inserts in one go. All items in the values array
     * must be keyed the same.
     *
     * @param string $table
     * @param array  $multiple_values
     * @param bool   $ignore
     */
    public function batchInsert($table, array $multiple_values, $ignore = false)
    {
        if (!$multiple_values) {
            throw new \InvalidArgumentException('No values');
        }

        //------------------------------
        // Validate values and build params
        //------------------------------

        $res                     = null;
        $multiple_values_batches = array_chunk($multiple_values, 1000, false);
        foreach ($multiple_values_batches as $multiple_values) {
            $cols       = null;
            $cols_count = 0;
            $params     = [];

            $value_parts = [];
            $value_tpl   = '';

            // TODO we need to create batches based on size of max_packet_size

            foreach ($multiple_values as $vals) {
                if ($cols === null) {
                    foreach (array_keys($vals) as $k) {
                        $cols[] = $k;
                    }
                    $cols_count = count($cols);
                    $value_tpl  = '('.implode(',', array_fill(0, $cols_count, '?')).')';
                }

                if (count($vals) != $cols_count) {
                    throw new \InvalidArgumentException('A value row has more columns than it should');
                }

                foreach ($cols as $c) {
                    if (!array_key_exists($c, $vals)) {
                        throw new \InvalidArgumentException("A value row is missing the `$c` column");
                    }

                    $params[] = $vals[$c];
                }

                $value_parts[] = $value_tpl;
            }

            //------------------------------
            // Build sql
            //------------------------------

            $sql = 'INSERT '.($ignore ? 'IGNORE' : '')." INTO `$table` (`".implode('`,`', $cols).'`) VALUES '.implode(',', $value_parts);

            $res = $this->executeUpdate($sql, $params);
        }

        if ($res === null) {
            throw new \InvalidArgumentException('No values');
        }

        return $res;
    }

    /**
     * Quote an array of values suitable for IN() clause.
     *
     * @param array $values
     * @param int   $type
     *
     * @return string
     */
    public function quoteIn(array $values, $type = null)
    {
        $quoted = [];

        foreach ($values as $val) {
            $quoted[] = $this->quote($val, $type);
        }

        $quoted = implode(',', $quoted);

        return $quoted;
    }

    /**
     * Just like insert() except uses INSERT IGNORE.
     *
     * @param string $tableName
     * @param array  $data
     * @param array  $types
     *
     * @return int
     */
    public function insertIgnore($tableName, array $data, array $types = [])
    {
        $this->connect();

        try {
            // column names are specified as array keys
            $cols         = [];
            $placeholders = [];

            foreach ($data as $columnName => $value) {
                $cols[]         = $columnName;
                $placeholders[] = '?';
            }

            $query = 'INSERT IGNORE INTO '.$tableName
                .' ('.implode(', ', $cols).')'
                .' VALUES ('.implode(', ', $placeholders).')';

            return $this->executeUpdate($query, array_values($data), $types);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Just like insert() except executes a REPLACE INTO instead.
     *
     * @param $tableName
     * @param array $data
     * @param array $types
     *
     * @return int
     */
    public function replace($tableName, array $data, array $types = [])
    {
        $this->connect();

        // column names are specified as array keys
        $cols         = [];
        $placeholders = [];

        foreach ($data as $columnName => $value) {
            $cols[]         = $columnName;
            $placeholders[] = '?';
        }

        $query = 'REPLACE INTO '.$tableName
               .' ('.implode(', ', $cols).')'
               .' VALUES ('.implode(', ', $placeholders).')';

        return $this->executeUpdate($query, array_values($data), $types);
    }

    /**
     * Fetch a COUNT(*) on $tableName with $where condition.
     *
     * @param string       $tableName
     * @param string|array $where     A string where or an array of field=>value
     */
    public function count($tableName, $where = null)
    {
        $qb = $this->createQueryBuilder()
            ->select('COUNT(*)')
            ->from($tableName, 't');
        $eb = $qb->expr();

        if ($where) {
            if (is_array($where)) {
                foreach ($where as $columnName => $value) {
                    $qb->andWhere($eb->eq($columnName, $value));
                }
            } else {
                $qb->where($where);
            }
        }

        return $qb->execute()->fetchColumn();
    }

    public function countWithPlaceholders($tableName, $where = null, array $params = [])
    {
        $this->connect();

        $where = $where ?: '1';
        $sql   = "SELECT COUNT(*) FROM `$tableName` WHERE ".$where;

        return $this->fetchColumn($sql, $params);
    }

    /**
     * @param string                                 $query
     * @param array                                  $params
     * @param array                                  $types
     * @param \Doctrine\DBAL\Cache\QueryCacheProfile $qcp
     * @param int                                    $is_retry
     *
     * @throws \Exception
     *
     * @return \Doctrine\DBAL\Cache\ArrayStatement|\Doctrine\DBAL\Cache\ResultCacheStatement|\Doctrine\DBAL\Driver\Statement
     */
    public function executeQuery($query, array $params = [], $types = [], \Doctrine\DBAL\Cache\QueryCacheProfile $qcp = null, $is_retry = 0)
    {
        try {
            return parent::executeQuery($query, $params, $types, $qcp);
        } catch (\Exception $e) {
            if ($e instanceof DBALException || $e instanceof \PDOException) {
                if ($is_retry <= 2 && (stripos($e->getMessage(), 'deadlock') !== false || stripos($e->getMessage(), 'wait timeout exceeded') !== false)) {
                    usleep(500000);

                    return $this->executeQuery($query, $params, $types, $qcp, $is_retry + 1);
                }

                $e->_dp_query        = $query;
                $e->_dp_query_params = $params;
                throw $e;
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param string $query
     * @param array  $params
     * @param array  $types
     */
    public function executeUpdate($query, array $params = [], array $types = [], $is_retry = 0)
    {
        $level = $this->getTransactionNestingLevel();
        if ($level && !$is_retry) {
            $this->writes_in_tx[] = [$query, $params, $types];
        }

        try {
            return parent::executeUpdate($query, $params, $types);
        } catch (\Exception $e) {
            if ($e instanceof DBALException || $e instanceof \PDOException) {
                if ($is_retry <= 2 && (stripos($e->getMessage(), 'deadlock') !== false || stripos($e->getMessage(), 'wait timeout exceeded') !== false)) {
                    usleep(500000);

                    return $this->executeUpdate($query, $params, $types, $is_retry + 1);
                }

                $e->_dp_query        = $query;
                $e->_dp_query_params = $params;
                throw $e;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Delete all records from table with an $field id in $ids.
     *
     * @param string       $table
     * @param array        $ids
     * @param string       $field
     * @param array|string $other_wheres
     *
     * @return int
     */
    public function deleteIn($table, array $ids, $field = 'id', $not = false, $other_wheres = '')
    {
        if (!$ids) {
            return 0;
        }

        if ($not) {
            $not = ' NOT ';
        } else {
            $not = '';
        }

        $more_where = '';
        if ($other_wheres) {
            if (is_array($other_wheres)) {
                $more_where = 'AND '.implode(' AND ', $other_wheres);
            } else {
                $more_where = 'AND '.$other_wheres;
            }
        }

        return $this->executeUpdate("DELETE FROM `$table` WHERE `$field` $not IN (".$this->quoteIn($ids).") $more_where");
    }

    /**
     * Update all records with $data with a $field id in $ids.
     *
     * @param string $table
     * @param array  $data
     * @param array  $ids
     * @param string $field
     * @param array  $types
     *
     * @return int
     */
    public function updateIn($table, array $data, array $ids, $field = 'id', array $types = [])
    {
        if (!$ids) {
            return 0;
        }

        $set = [];
        foreach ($data as $columnName => $value) {
            $set[] = $columnName.' = ?';
        }

        $params = array_values($data);

        $sql = "UPDATE `$table` SET ".implode(', ', $set)." WHERE `$field` IN (".$this->quoteIn($ids).')';

        return $this->executeUpdate($sql, $params, $types);
    }

    /**
     * @param string $statement
     *
     * @return int
     */
    public function exec($statement)
    {
        try {
            return parent::exec($statement);
        } catch (\Exception $e) {
            if ($e instanceof DBALException || $e instanceof \PDOException) {
                $e->_dp_query        = is_string($statement) ? $statement : null;
                $e->_dp_query_params = [];
                throw $e;
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param string $statement
     *
     * @return Statement
     */
    public function prepare($statement)
    {
        $this->connect();

        return new Statement($statement, $this);
    }

    public function beginTransaction()
    {
        $level = $this->getTransactionNestingLevel();
        if ($level == 0) {
            $this->writes_in_tx = [];
        }

        parent::beginTransaction();
        if ($this->transaction_logger) {
            $e                 = new \Exception();
            $backtrace         = \DpSys\LowError\SystemErrorHandler::formatBacktrace($e->getTrace());
            $level             = $this->getTransactionNestingLevel();
            $trans_id          = \Orb\Util\Util::baseEncode($this->trans_count++, \Orb\Util\Strings::CHARS_ALPHA_IU);
            $this->trans_ids[] = $trans_id;
            $backtrace         = \Orb\Util\Strings::modifyLines($backtrace, str_repeat("\t\t", $level)."\t\t");
            $this->transaction_logger->logDebug("==> Level $level :: <$trans_id>\n".str_repeat("\t\t", $level)."TRANSACTION BEGIN\n$backtrace");
        }
    }

    public function commit($is_retry = 0)
    {
        try {
            parent::commit();
        } catch (\Exception $e) {
            if ($this->writes_in_tx && $is_retry <= 1 && (stripos($e->getMessage(), 'deadlock') !== false || stripos($e->getMessage(), 'wait timeout exceeded') !== false)) {
                usleep(500000);

                $retry              = $this->writes_in_tx;
                $this->writes_in_tx = [];

                // Retry the trans
                $this->_conn->beginTransaction();
                foreach ($retry as $q) {
                    $this->executeUpdate($q[0], $q[1], $q[2], 1);
                }
                $this->commit(true);
            } else {
                $this->writes_in_tx = [];
                throw $e;
            }
        }

        $level = $this->getTransactionNestingLevel();
        if ($level == 0) {
            $this->writes_in_tx = [];
        }

        if (!$this->running_trans_event && $this->_eventManager->hasListeners(self::EVENT_POST_COMMIT)) {
            $this->running_trans_event = true;
            $eventArgs                 = new Event\PostCommit($this);
            $this->_eventManager->dispatchEvent(self::EVENT_POST_COMMIT, $eventArgs);
            $this->running_trans_event = false;
        }

        if ($this->transaction_logger) {
            $e         = new \Exception();
            $trans_id  = array_pop($this->trans_ids);
            $backtrace = \DpSys\LowError\SystemErrorHandler::formatBacktrace($e->getTrace());
            $backtrace = \Orb\Util\Strings::modifyLines($backtrace, str_repeat("\t\t", $level)."\t\t");
            $this->transaction_logger->logDebug("<== Level $level :: <$trans_id>\n".str_repeat("\t\t", $level)."TRANSACTION COMMITTED\n$backtrace");
        }

        if (!$this->getTransactionNestingLevel()) {
            // Set in EntityWatcher
            // If we have got here with a successful commit, then the changes are now
            // properly synced and we dont need the flag set anymore
            unset($GLOBALS['DP_HAS_UPDATED_SEARCH_TABLES']);

            if ($this->do_reset_isolation) {
                $this->do_reset_isolation = false;
                $this->setIsolationDefault();
            }

            \DpShutdown::run('db_done_trans_commit');
            \DpShutdown::run('db_done_trans');
        }
    }

    public function rollback($is_unexpected = true)
    {
        try {
            parent::rollback();
        } catch (\Exception $e) {
            $einfo = \DpSys\LowError\SystemErrorHandler::getExceptionInfo($e);
            \DpSys\LowError\SystemErrorHandler::logErrorInfo($einfo);

            return;
        }

        $level = $this->getTransactionNestingLevel();
        if ($level == 0) {
            $this->writes_in_tx = [];
        }

        if ($is_unexpected) {
            if (!$this->running_trans_event && $this->_eventManager->hasListeners(self::EVENT_POST_ROLLBACK)) {
                $this->running_trans_event = true;
                $eventArgs                 = new Event\PostCommit($this);
                $this->_eventManager->dispatchEvent(self::EVENT_POST_ROLLBACK, $eventArgs);
                $this->running_trans_event = false;
            }

            if ($this->transaction_logger) {
                $e         = new \Exception();
                $backtrace = \DpSys\LowError\SystemErrorHandler::formatBacktrace($e->getTrace());
                $backtrace = \Orb\Util\Strings::modifyLines($backtrace, str_repeat("\t", $level)."\t");
                $this->transaction_logger->logDebug(str_repeat("\t", $level)."TRANSACTION ROLLED BACK\n$backtrace");
            }
        }

        if (!$level) {
            if ($this->do_reset_isolation) {
                $this->do_reset_isolation = false;
                $this->setIsolationDefault();
            }
            \DpShutdown::run('db_done_trans_rollback');
            \DpShutdown::run('db_done_trans');
        }
    }

    /**
     * Set isolation level to REPEATABLE READ.
     *
     * @param bool $auto_reset True to auto-reset the isolation after the current transaction ends
     */
    public function setIsolationRepeatableRead($auto_reset = false)
    {
        $this->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        if ($auto_reset) {
            $this->do_reset_isolation = true;
        }
    }

    /**
     * Set isolation level to READ COMMITTED.
     *
     * @param bool $auto_reset True to auto-reset the isolation after the current transaction ends
     */
    public function setIsolationReadCommitted($auto_reset = false)
    {
        $this->exec('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        if ($auto_reset) {
            $this->do_reset_isolation = true;
        }
    }

    /**
     * Set isolation level back to default (REPEATABLE READ usually).
     */
    public function setIsolationDefault()
    {
        $this->exec("SET SESSION TRANSACTION ISOLATION LEVEL {$this->default_isolation}");
    }
}
