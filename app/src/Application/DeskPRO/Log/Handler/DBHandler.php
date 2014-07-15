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


namespace Application\DeskPRO\Log\Handler;


use Application\DeskPRO\DBAL\Connection;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

abstract class DBHandler extends AbstractProcessingHandler
{
	private $initialized = false;
	protected $connection;
	protected $statement;

	public function __construct(Connection $connection, $level = Logger::DEBUG, $bubble = true)
	{
		$this->connection = $connection;
		parent::__construct($level, $bubble);
	}

	/**
	 * table name
	 * @return string
	 */
	abstract protected function getTableName();

	/**
	 * todo move to upgrade
	 * @return mixed
	 */
	abstract protected function initializeSchema();

	/**
	 * return fields map
	 * @param $record
	 * @return array
	 */
	abstract protected function mapRecord($record = array());

	protected function prepareStatement()
	{
		$keys = array_keys($this->mapRecord());
		$this->statement = $this->connection->prepare(sprintf(
			'INSERT INTO %s (%s) VALUES (%s)',
			$this->getTableName(),
			implode(', ', $keys),
			':' . implode(', :', $keys)
		));
	}

	/**
	 * @param array $record
	 */
	protected function write(array $record)
	{
		if (!$this->initialized) {
			$this->initialize();
		}

		$map = $this->mapRecord($record);
		$this->statement->execute($map);
	}

	private function initialize()
	{
		$this->initializeSchema();
		$this->prepareStatement();
		$this->initialized = true;
	}
}
