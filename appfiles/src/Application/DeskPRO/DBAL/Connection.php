<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DBAL;

use \PDO;

/**
 * Some enhancements to Doctrine's connection class.
 */
class Connection extends \Doctrine\DBAL\Connection
{
	protected $_max_packet_size = null;

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
				$array[$row[$group_key]][$row[$index]] = $val;
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
	public function fetchAllKeyValue($statement, array $params = array(), $key_index = 0, $val_index = 1, $mode = PDO::FETCH_NUM)
	{
		$statement = $this->executeQuery($statement, $params);
		$array = array();

		while ($row = $statement->fetch($mode)) {
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
}
