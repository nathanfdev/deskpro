<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\DBAL;


/**
 * Some enhancements to Doctrine's connection class.
 */
class Connection extends \Doctrine\DBAL\Connection
{
	/**
	 * Execute a query and return all results indexed with the specified column.
	 *
	 * @param string $statement
	 * @param array $params
	 * @param string $index
	 * @return array
	 */
	public function fetchArrayKeyed($statement, array $params = array(), $index = 'id')
	{
		$statement = $this->executeQuery($statement, $params);
		$array = array();

		while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$array[$row[$index]] = $row;
		}

		return $array;
	}
}
