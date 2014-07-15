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
use Application\DeskPRO\Log\Entry\RoundRobinEntry;
use Monolog\Logger;

class RoundRobinHandler extends DBHandler
{
	public function __construct(Connection $connection, $level = Logger::DEBUG, $bubble = true)
	{
		// todo maybe we should prevent log bubbling for this instance
		parent::__construct($connection, $level, $bubble);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isHandling(array $record)
	{
		return isset($record['context']['entry']) && $record['context']['entry'] instanceof RoundRobinEntry;
	}

	protected function getTableName()
	{
		return 'log__round_robin';
	}

	/**
	 * @param array $record
	 * @return array
	 */
	protected function mapRecord($record = array())
	{
		$map = array(
			'timestamp' => null,
			'round_robin_id' => null,
			'agent_id' => null,
			'ticket_id' => null,
			'trigger_id' => null,
		);

		if ($record){
			/** @var RoundRobinEntry $entry */
			$entry = $record['context']['entry'];

			$map['timestamp'] = $record['datetime']->getTimestamp();
			$map['round_robin_id'] = $entry->round_robin_id;
			$map['agent_id'] = $entry->agent_id;
			$map['ticket_id'] = $entry->ticket_id;
			$map['trigger_id'] = $entry->trigger_id;
		}

		return $map;
	}

	protected function initializeSchema()
	{
		// todo schema creation should be in upgrade script
		$this->connection->exec(sprintf('
			CREATE TABLE IF NOT EXISTS `%s` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `timestamp` int(11) unsigned NOT NULL,
			  `round_robin_id` int(11) unsigned NOT NULL,
			  `agent_id` int(11) unsigned NOT NULL,
			  `ticket_id` int(11) unsigned NOT NULL,
			  `trigger_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB
		', $this->getTableName()));
	}
} 