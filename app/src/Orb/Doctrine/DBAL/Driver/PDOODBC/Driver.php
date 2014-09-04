<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
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
 * Orb
 *
 * @package    Orb
 * @subpackage Doctrine
 */

namespace Orb\Doctrine\DBAL\Driver\PDOODBC;

class Driver implements \Doctrine\DBAL\Driver
{
	/**
	 * Platform or platform class. Common: Server2008Platform, SQLServer2005Platform
	 * @var string
	 */
	private $platform;


	/**
	 * @param array        $params
	 * @param string|null  $username
	 * @param string|null  $password
	 * @param array        $driverOptions
	 * @return \Doctrine\DBAL\Driver\Connection|Connection
	 */
	public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
	{
		$this->platform = $params['platform'];
		return new Connection(
			$this->_constructPdoDsn($params),
			$username,
			$password,
			$driverOptions
		);
	}


	/**
	 * Constructs the ODBC PDO DSN.
	 *
	 * @param array $params
	 * @return string  The DSN.
	 */
	private function _constructPdoDsn(array $params)
	{
		$dsn = 'odbc:' . $params['dsn'];
		return $dsn;
	}


	/**
	 * @return \Doctrine\DBAL\Platforms\AbstractPlatform
	 * @throws \InvalidArgumentException
	 */
	public function getDatabasePlatform()
	{
		$classname = $this->platform;
		$default_classname = 'Doctrine\\DBAL\\Platforms\\' . $this->platform;

		if (class_exists($default_classname)) {
			return new $default_classname();
		} else if (class_exists($classname)) {
			return new $classname();
		} else {
			throw new \InvalidArgumentException();
		}
	}


	/**
	 * @param \Doctrine\DBAL\Connection $conn
	 * @return \Doctrine\DBAL\Schema\AbstractSchemaManager|\Doctrine\DBAL\Schema\SQLServerSchemaManager
	 */
	public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
	{
		return new \Doctrine\DBAL\Schema\SQLServerSchemaManager($conn);
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return 'pdo_odbc';
	}


	/**
	 * @param \Doctrine\DBAL\Connection $conn
	 * @return string
	 */
	public function getDatabase(\Doctrine\DBAL\Connection $conn)
	{
		$params = $conn->getParams();
		return $params['dbname'];
	}
}
