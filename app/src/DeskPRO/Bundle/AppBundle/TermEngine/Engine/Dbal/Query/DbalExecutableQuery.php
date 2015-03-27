<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use Doctrine\DBAL\Connection;

class DbalExecutableQuery
{
    /**
     * @var DbalCompiledQuery
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

    public function __construct(DbalCompiledQuery $query, Connection $connection)
    {
        $this->query = $query;
        $this->connection = $connection;
    }

    /**
     * Fetches the entity ids like: [ [ 'id' => 1 ], [ 'id' => 2 ] ]
     *
     * @return array
     */
    public function fetchIds()
    {
        $query = clone $this->query;

        $query->setSelectPart('{from}.id');

        $stmt = $this->execute($query);

        return $stmt->fetchAll();
    }

    /**
     * Gets the total count, ignoring any pagination
     *
     * @return int
     */
    public function fetchCount()
    {
        $query = clone $this->query;

        $query->setSelectPart('COUNT(*) AS count');
        $query->setPage(null);
        $query->setLimit(null);

        $stmt = $this->execute($query);

        $res = $stmt->fetch();

        if (!is_array($res) || !isset($res['count'])) {
            return 0;
        }

        return (int)$res['count'];
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
     * @return array|null
     */
    public function getLastRunParameterTypes()
    {
        return $this->last_run_parameter_types;
    }

    /**
     * @param DbalCompiledQuery $query
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(DbalCompiledQuery $query)
    {
        $this->last_run_sql = (string)$query;
        $this->last_run_parameters = $query->getParameters();
        $this->last_run_parameter_types = $this->determineParameterTypes($this->last_run_parameters);

        $stmt = $this->connection->executeQuery(
            $this->last_run_sql,
            $this->last_run_parameters,
            $this->last_run_parameter_types
        );

        return $stmt;
    }

    /**
     * Get an array of pdo/dbal types to use to execute the query
     *
     * @param array $params
     * @return array
     */
    public function determineParameterTypes(array $params)
    {
        $types = array();

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
                        $num_int++;
                    } elseif ($this->isStr($child)) {
                        $num_str++;
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
            $param = (string)$param;

            if (is_string($param)) {
                $types[] = \PDO::PARAM_STR;
                continue;
            }

            throw new \InvalidArgumentException('unable to determine param type in DbalExecutableQuery');
        }

        return $types;
    }

    /**
     * @param $param
     * @return bool
     */
    private function isNum($param)
    {
        return is_int($param) || is_float($param) || (is_numeric($param) && !is_string($param));
    }

    /**
     * @param $param
     * @return bool
     */
    private function isStr($param)
    {
        return is_string($param);
    }
}
