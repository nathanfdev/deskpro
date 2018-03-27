<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use DeskPRO\Bundle\AppBundle\Util\SimpleTimer;
use Doctrine\DBAL\Connection;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class DbalExecutableQuery.
 */
class DbalExecutableQuery
{
    const GROUPS_LIMIT = 100;

    public static $groupAliases = [
        'agent'        => '{from}.agent_id',
        'department'   => '{from}.department_id',
        'person'       => '{from}.person_id',
        'date_created' => '{from}.date_created',
    ];

    /**
     * @var DbalQuery
     */
    private $query;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string|null
     */
    private $last_run_sql;

    /**
     * @var array|null
     */
    private $last_run_parameters;

    /**
     * @var array|null
     */
    private $last_run_parameter_types;

    private $order_by;
    private $page;
    private $offset;
    private $count;
    private $and_where;
    private $and_group_where;
    private $group_by;

    /**
     * additional custom select fields; defined as:
     *     ['alias' => 'SQL bit'].
     *
     * @var array
     */
    private $additional_selects;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param DbalQuery       $query
     * @param Connection      $connection
     * @param LoggerInterface $logger
     */
    public function __construct(DbalQuery $query, Connection $connection, LoggerInterface $logger = null)
    {
        $this->query      = $query;
        $this->connection = $connection;

        $this->order_by           = [];
        $this->and_where          = [];
        $this->and_group_where    = [];
        $this->group_by           = [];
        $this->page               = 1;
        $this->count              = null;
        $this->logger             = $logger;
        $this->additional_selects = [];
    }

    /**
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    protected function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $message = sprintf('DbalExecutableQuery: %s', $message);
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Fetches all the paged tickets in an array like: [Ticket, Ticket].
     *
     * @return array
     */
    public function fetchAll()
    {
        $timer = new SimpleTimer();

        $this->log(Logger::DEBUG, 'fetchIds() called');

        $query = clone $this->query;
        // pagination
        if ($this->count) {
            $this->log(Logger::DEBUG, 'adding pagination to query', [
                'page'   => $this->page,
                'offset' => $this->offset,
                'count'  => $this->count,
            ]);

            $query->setPage($this->page);
            $query->setOffset($this->offset);
            $query->setLimit($this->count);
        }

        // ordering
        foreach ($this->order_by as $order_by => $direction) {
            $this->log(Logger::DEBUG, 'add order by', ['by' => $order_by, 'dir' => $direction]);
            $query->addOrderBy($order_by, $direction);
        }

        // various WHERE manipulations
        $this->manipulateWhere($query);
        $stmt = $this->execute($query);
        $all  = $stmt->fetchAll();

        $this->log(Logger::DEBUG, 'row count', ['count' => $stmt->rowCount()]);
        $this->log(Logger::DEBUG, 'finished fetechAll()', ['time' => $timer->getElapsedTime()]);

        return $all;
    }

    /**
     * Fetches the entity ids like: [ [ 'id' => 1 ], [ 'id' => 2 ] ].
     *
     * @return array
     */
    public function fetchIds()
    {
        $timer = new SimpleTimer();

        $this->log(Logger::DEBUG, 'fetchIds() called');

        $query = clone $this->query;

        $this->log(Logger::DEBUG, 'mutating query to select IDs');
        $query->setSelectPart('distinct {from}.id');

        // pagination
        if ($this->count) {
            $this->log(Logger::DEBUG, 'adding pagination to query', [
                'page'   => $this->page,
                'offset' => $this->offset,
                'count'  => $this->count,
            ]);

            $query->setPage($this->page);
            $query->setOffset($this->offset);
            $query->setLimit($this->count);
        }

        // ordering
        foreach ($this->order_by as $order_by => $direction) {
            $this->log(Logger::DEBUG, 'add order by', ['by' => $order_by, 'dir' => $direction]);
            $query->addOrderBy($order_by, $direction);
        }

        // various WHERE manipulations
        $this->manipulateWhere($query);

        $stmt = $this->execute($query);
        $ids  = [];

        foreach ($stmt->fetchAll() as $row) {
            $ids[] = $row['id'];
        }

        $this->log(Logger::DEBUG, 'selected IDs', ['ids' => $ids]);
        $this->log(Logger::DEBUG, 'row count', ['count' => $stmt->rowCount()]);
        $this->log(Logger::DEBUG, 'finished fetchIds()', ['time' => $timer->getElapsedTime()]);

        return $ids;
    }

    /**
     * Gets the total count, ignoring any pagination.
     *
     * @return int
     */
    public function fetchCount()
    {
        $query = clone $this->query;

        $query->setSelectPart('COUNT(distinct ticket.id) AS count');
        $query->setPage(null);
        $query->setOffset(null);
        $query->setLimit(null);

        // various WHERE manipulations
        $this->manipulateWhere($query);

        $stmt = $this->execute($query);

        $res = $stmt->fetch();

        if (!is_array($res) || !isset($res['count'])) {
            return 0;
        }

        return (int) $res['count'];
    }

    /**
     * @return array
     */
    public function fetchGroupedCount()
    {
        $query = clone $this->query;

        $query->setSelectPart('COUNT(ticket.id) AS count');

        // group by
        if (count($this->group_by) > 0) {
            $query->setGroupWithRollup(false);
            foreach ($this->group_by as $alias => $group) {
                if (!array_key_exists($alias, $this->additional_selects)) {
                    $query->addSelectPart(sprintf('%s AS %s', $group, $alias));
                }

                $query->addGroupBy($alias);
            }
        }

        if (count($this->additional_selects) > 0) {
            foreach ($this->additional_selects as $alias => $sql) {
                if (is_numeric($alias) || !trim($alias)) {
                    continue; // We don't want ridiculous aliases.
                }
                $query->addSelectPart(sprintf('(%s) as %s', $sql, $alias));
            }
        }

        // ordering
        if ($this->order_by) {
            foreach ($this->order_by as $order_by => $direction) {
                $this->log(Logger::DEBUG, 'add order by', ['by' => $order_by, 'dir' => $direction]);
                $query->addOrderBy($order_by, $direction);
            }
        } else { // by default order by count to show bigger ones first and to not skip them with self::GROUPS_LIMIT
            $query->addOrderBy('count', 'desc');
        }

        // various WHERE manipulations
        $this->manipulateWhere($query);

        $query->setPage(null);
        $query->setOffset(null);
        $query->setLimit(self::GROUPS_LIMIT);

        $stmt   = $this->execute($query);
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * @return null|string
     */
    public function getLastRunSql()
    {
        return $this->last_run_sql;
    }

    /**
     * @return null|string
     */
    public function getLastRunParameters()
    {
        return $this->last_run_parameters;
    }

    /**
     * @param string $group
     * @param string $optional_select_alias
     */
    public function addCountGroup($group, $optional_select_alias = null)
    {
        if (null === $optional_select_alias) {
            $sql = $this->transformAliasGroupName($group);
            if ($sql) {
                $optional_select_alias = $group;
                $group                 = $sql;
            } else {
                $optional_select_alias = $group;
            }
        } elseif (is_array($optional_select_alias)) {
            $this->addSelect($optional_select_alias['alias'], $optional_select_alias['sql']);
            $optional_select_alias = $optional_select_alias['alias'];
        }

        $this->log(Logger::DEBUG, 'adding count group', ['group' => $group, 'alias' => $optional_select_alias]);

        $this->group_by[$optional_select_alias] = $group;
    }

    /**
     * Adds a custom select item.
     *
     * @param string $alias is the new select item's alias
     * @param string $sql   is the sql clause that defines the new select item
     *
     * @return $this
     */
    public function addSelect($alias, $sql)
    {
        $this->additional_selects[$alias] = $sql;

        return $this;
    }

    /**
     * @return array
     */
    public function getGroupBy()
    {
        return $this->group_by;
    }

    /**
     * @return array|null
     */
    public function getLastRunParameterTypes()
    {
        return $this->last_run_parameter_types;
    }

    /**
     * @param DbalQuery $query
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    protected function execute(DbalQuery $query)
    {
        $this->last_run_sql             = (string) $query;
        $this->last_run_parameters      = $query->getParameters();
        $this->last_run_parameter_types = $this->determineParameterTypes($this->last_run_parameters);

        $timer = new SimpleTimer();

        $this->log(Logger::DEBUG, 'running execution', [
            'sql'    => $this->last_run_sql,
            'params' => $this->last_run_parameters,
        ]);

        $stmt = $this->connection->executeQuery(
            $this->last_run_sql,
            $this->last_run_parameters,
            $this->last_run_parameter_types
        );

        $this->log(Logger::DEBUG, 'finished execution', [
            'time' => $timer->getElapsedTime(),
        ]);

        return $stmt;
    }

    /**
     * Get an array of pdo/dbal types to use to execute the query.
     *
     * @param array $params
     *
     * @return array
     */
    public function determineParameterTypes(array $params)
    {
        $types = [];

        foreach ($params as $key => $param) {
            if ($this->isNum($param)) {
                $types[$key] = \PDO::PARAM_INT;
                continue;
            }

            if ($this->isStr($param)) {
                $types[$key] = \PDO::PARAM_STR;
                continue;
            }

            if (is_array($param)) {
                $num_str = 0;
                $num_int = 0;

                // we need to determine if its all ints or not
                foreach ($param as $child) {
                    if ($this->isNum($child)) {
                        ++$num_int;
                    } elseif ($this->isStr($child)) {
                        ++$num_str;
                    }
                }

                // its an array of ints
                if ($num_int && !$num_str) {
                    $types[$key] = Connection::PARAM_INT_ARRAY;
                    continue;
                }

                // else, use array of strings
                $types[$key] = Connection::PARAM_STR_ARRAY;
                continue;
            }

            // last attempt before erroring
            $param = (string) $param;

            if (is_string($param)) {
                $types[] = \PDO::PARAM_STR;
                continue;
            }

            throw new \InvalidArgumentException('unable to determine param type in DbalExecutableQuery');
        }

        $this->log(Logger::DEBUG, 'determining dbal types for params', ['params' => $params]);
        $this->log(Logger::DEBUG, 'determined types', ['types' => $types]);

        return $types;
    }

    /**
     * @param $param
     *
     * @return bool
     */
    protected function isNum($param)
    {
        return is_int($param) || is_float($param) || (is_numeric($param) && !is_string($param));
    }

    /**
     * @param $param
     *
     * @return bool
     */
    protected function isStr($param)
    {
        return is_string($param);
    }

    /**
     * @param string $potentially_an_alias
     *
     * @return string
     */
    protected function transformAliasGroupName($potentially_an_alias)
    {
        if (!array_key_exists($potentially_an_alias, self::$groupAliases)) {
            return '';
        }

        return self::$groupAliases[$potentially_an_alias];
    }

    /**
     * @return mixed
     */
    public function getOrderBy()
    {
        return $this->order_by;
    }

    /**
     * @param $order_by
     * @param $dir
     */
    public function addOrderBy($order_by, $dir)
    {
        $this->order_by[$order_by] = $dir;
    }

    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param null $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return array
     */
    public function getAndWhere()
    {
        return $this->and_where;
    }

    /**
     * @param array $and_where
     */
    public function addAndWhere($and_where)
    {
        $this->and_where[] = $and_where;
    }

    /**
     * @return array
     */
    public function getAndGroupWhere()
    {
        return $this->and_group_where;
    }

    /**
     * @param $group_alias
     * @param $value
     */
    public function addAndGroupWhere($group_alias, $value)
    {
        $this->and_group_where[$group_alias] = $value;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param mixed $offset
     *
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Add custom field table join.
     *
     * @param int $field_id
     */
    public function addCustomFieldTableJoins($field_id)
    {
        if (!$this->query->hasJoin('custom_data_ticket') && !$this->query->hasJoin('custom_def_ticket')) {
            $this->query->addJoin(
                'custom_data_ticket',
                'custom_data_ticket.ticket_id = ticket.id'
            );
            $this->query->addJoin(
                'custom_def_ticket',
                'custom_data_ticket.field_id = custom_def_ticket.id'
            );
            $this->query->appendWhere(
                sprintf('AND (custom_def_ticket.id = %d)', (int) $field_id));
        }
    }

    /**
     * @param DbalQuery $query
     */
    protected function manipulateWhere(DbalQuery $query)
    {
        // extra where
        foreach ($this->and_where as $where) {
            $this->log(Logger::DEBUG, 'adding AND where', ['where' => $where]);
            $query->appendWhere(sprintf('AND (%s)', $where));
        }

        // group where (allows setting a special group field to a value in the where clause)
        foreach ($this->and_group_where as $group_name => $value) {
            $param_name = $query->addParameter('group_name', $value);
            $and_clause = sprintf('(%s = :%s)', $this->transformAliasGroupName($group_name), $param_name);
            $this->log(Logger::DEBUG, 'and group by clause', ['where' => $and_clause]);
            $query->appendWhere('AND '.$and_clause);
        }
    }
}
