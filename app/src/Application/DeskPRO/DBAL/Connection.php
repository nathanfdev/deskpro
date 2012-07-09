<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @category Controller
 */

namespace Application\DeskPRO\DBAL;

use PDO;

/**
 * Some enhancements to Doctrine's connection class.
 */
class Connection extends \Doctrine\DBAL\Connection
{
	const EVENT_POST_COMMIT   = 'onPostCommit';
	const EVENT_POST_ROLLBACK = 'onPostRollback';

	protected $_max_packet_size = null;

	protected $running_trans_event = false;

	public function __construct(array $params, \Doctrine\DBAL\Driver $driver, \Doctrine\DBAL\Configuration $config = null, \Doctrine\Common\EventManager $eventManager = null)
	{
		if (!isset($params['driverOptions'])) {
			$params['driverOptions'] = array();
		}

		$params['driverOptions'][PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
		$params['driverOptions'][PDO::ATTR_EMULATE_PREPARES] = true;

		if (!isset($params['platform'])) {
			$params['platform'] = new \Application\DeskPRO\DBAL\Platforms\MySqlPlatform();
		}

		$m = null;
		if (isset($params['host']) && preg_match('#^(.*?):([0-9]+)$#', $params['host'], $m)) {
			$params['host'] = $m[1];
			$params['port'] = $m[2];
		}

		parent::__construct($params, $driver, $config, $eventManager);

		$db = $this;
		\DpShutdown::add(function() use($db) {
			if ($db->isTransactionActive()) {
				$level = $db->getTransactionNestingLevel();
				$e = new ShutdownTransactionActiveException("Database transaction is still active ($level deep)");

				$errinfo = \DeskPRO\Kernel\KernelErrorHandler::getExceptionInfo($exception);
				\DeskPRO\Kernel\KernelErrorHandler::logErrorInfo($errinfo);
			}
		});
	}

	public function connect()
	{
		if (parent::connect()) {
			$this->exec("SET sql_mode=''");
			$this->exec("SET NAMES 'UTF8'");
			return true;
		}

		return false;
	}

	/**
	 * Gets the max packet size.
	 *
	 * @return int
	 */
	public function getMaxPacketSize()
	{
		if ($this->_max_packet_size !== null) return $this->_max_packet_size;

		$result = $this->fetchAssoc("SHOW variables LIKE 'max_allowed_packet'");
		$this->_max_packet_size = $result['Value'];

		return $this->_max_packet_size;
	}



	/**
	 * Execute a query and return all results indexed with the specified column.
	 *
	 * @param string $statement
	 * @param array $params
	 * @param string $index
	 * @return array
	 */
	public function fetchAllKeyed($statement, array $params = array(), $index = 'id')
	{
		$statement = $this->executeQuery($statement, $params);
		$array = array();

		while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$array[$row[$index]] = $row;
		}


		return $array;
	}



	/**
	 * Execute a query and return all results grouped into a multi-dimentional array by $group_key.
	 * Optionally, the sub-array can be indexed by $index_key.
	 *
	 * @param string $statement
	 * @param array $params
	 * @param string $group_key
	 * @param string $index_key
	 */
	public function fetchAllGrouped($statement, array $params = array(), $group_key, $index_key = null, $col_key = null)
	{
		$statement = $this->executeQuery($statement, $params);
		$array = array();

		while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			if (!isset($array[$row[$group_key]])) $array[$row[$group_key]] = array();

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
	 * @param string $statement
	 * @param array $params
	 * @param string $key_index
	 * @param string $val_index
	 * @param int $mode Change to PDO::FETCH_ASSOC if you want to specify a string indexes
	 * @return array
	 */
	public function fetchAllKeyValue($statement, array $params = array(), $key_index = 0, $val_index = 1, $mode = PDO::FETCH_NUM, $nullkey = 0)
	{
		$statement = $this->executeQuery($statement, $params);
		$array = array();

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
	 * @param array $params
	 * @param string $index
	 * @param int $mode Change to PDO::FETCH_ASSOC if you want to specify a string $index
	 * @return array
	 */
	public function fetchAllCol($statement, array $params = array(), $index = 0, $mode = PDO::FETCH_NUM)
	{
		$statement = $this->executeQuery($statement, $params);
		$array = array();

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
	 * @param array $multiple_values
	 */
	public function batchInsert($table, array $multiple_values)
	{
		$cols = null;
		$cols_count = 0;
		$params = array();

		$value_parts = array();
		$value_tpl = '';

		if (!$multiple_values) {
			throw new \InvalidArgumentException("No values");
		}

		#------------------------------
		# Validate values and build params
		#------------------------------

		foreach ($multiple_values as $vals) {
			if ($cols === null) {
				foreach (array_keys($vals) as $k) {
					$cols[] = $k;
				}
				$cols_count = count($cols);
				$value_tpl = '(' . implode(',', array_fill(0, $cols_count, '?')) . ')';
			}

			if (count($vals) != $cols_count) {
				throw new \InvalidArgumentException("A value row has more columns than it should");
			}

			foreach ($cols as $c) {
				if (!array_key_exists($c, $vals)) {
					throw new \InvalidArgumentException("A value row is missing the `$c` column");
				}

				$params[] = $vals[$c];
			}

			$value_parts[] = $value_tpl;
		}

		#------------------------------
		# Build sql
		#------------------------------

		$sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) ."`) VALUES " . implode(',', $value_parts);

		return $this->executeUpdate($sql, $params);
	}


	/**
	 * Quote an array of values suitable for IN() clause.
	 *
	 * @param array $values
	 * @param int $type
	 * @return string
	 */
	public function quoteIn(array $values, $type = null)
	{
		$quoted = array();

		foreach ($values as $val) {
			$quoted[] = $this->quote($val, $type);
		}

		$quoted = implode(',', $quoted);

		return $quoted;
	}


	/**
	 * Just like insert() except executes a REPLACE INTO instead.
	 *
	 * @param $tableName
	 * @param array $data
	 * @param array $types
	 */
	public function replace($tableName, array $data, array $types = array())
	{
		$this->connect();

		// column names are specified as array keys
		$cols = array();
		$placeholders = array();

		foreach ($data as $columnName => $value) {
			$cols[] = $columnName;
			$placeholders[] = '?';
		}

		$query = 'REPLACE INTO ' . $tableName
			   . ' (' . implode(', ', $cols) . ')'
			   . ' VALUES (' . implode(', ', $placeholders) . ')';

		return $this->executeUpdate($query, array_values($data), $types);
	}


	/**
	 * @param string $query
	 * @param array $params
	 * @param array $types
	 * @param \Doctrine\DBAL\Cache\QueryCacheProfile|null $qcp
	 */
	public function executeQuery($query, array $params = array(), $types = array(), \Doctrine\DBAL\Cache\QueryCacheProfile $qcp = null)
	{
		try {
			return parent::executeQuery($query, $params, $types, $qcp);
		} catch (\PDOException $e) {
			$e->_dp_query = $query;
			$e->_dp_query_params = $params;
			throw $e;
		}
	}


	/**
	 * @param string $query
	 * @param array $params
	 * @param array $types
	 */
	public function executeUpdate($query, array $params = array(), array $types = array())
	{
		try {
			return parent::executeUpdate($query, $params, $types);
		} catch (\PDOException $e) {
			$e->_dp_query = $query;
			$e->_dp_query_params = $params;
			throw $e;
		}
	}


	/**
	 * @param string $statement
	 * @return int
	 */
	public function exec($statement)
	{
		try {
			return parent::exec($statement);
		} catch (\PDOException $e) {
			$e->_dp_query = is_string($statement) ? $statement : null;
			$e->_dp_query_params = array();
			throw $e;
		}
	}


	/**
	 * @param string $statement
	 * @return Statement
	 */
	public function prepare($statement)
	{
		$this->connect();

		return new Statement($statement, $this);
	}


	public function commit()
	{
		parent::commit();

		if (!$this->running_trans_event && $this->_eventManager->hasListeners(self::EVENT_POST_COMMIT)) {
			$this->running_trans_event = true;
			$eventArgs = new Event\PostCommit($this);
			$this->_eventManager->dispatchEvent(self::EVENT_POST_COMMIT, $eventArgs);
			$this->running_trans_event = false;
		}
	}

	public function rollback()
	{
		parent::rollback();

		if (!$this->running_trans_event && $this->_eventManager->hasListeners(self::EVENT_POST_ROLLBACK)) {
			$this->running_trans_event = true;
			$eventArgs = new Event\PostCommit($this);
			$this->_eventManager->dispatchEvent(self::EVENT_POST_ROLLBACK, $eventArgs);
			$this->running_trans_event = false;
		}
	}
}
