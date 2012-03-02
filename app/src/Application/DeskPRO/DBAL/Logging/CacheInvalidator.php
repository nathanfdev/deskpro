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
 * @subpackage DBAL
 */

namespace Application\DeskPRO\DBAL\Logging;

use Application\DeskPRO\App;

/**
 * This checks queries and invalidates certain caches that UPDATE key tables.
 */
class CacheInvalidator implements \Doctrine\DBAL\Logging\SQLLogger
{
	/**
	 * This is a table_name => RepositoryName we'll use to call invalidateFromQuery($sql)
	 *
	 * @var array
	 */
	protected $cache_tables = array(
		'agent_teams'        => 'DeskPRO:AgentTeam',
		'departments'        => 'DeskPRO:Department',
		'organizations'      => 'DeskPRO:Organization',
		'products'           => 'DeskPRO:Product',
		'ticket_categories'  => 'DeskPRO:TicketCategory',
		'ticket_priorities'  => 'DeskPRO:TicketPriority',
		'ticket_workflows'   => 'DeskPRO:TicketWorkflow',
		'usergroups'         => 'DeskPRO:Usergroup',
		'settings'           => 'DeskPRO:Setting',
	);

	public function startQuery($sql, array $params = null, array $types = null)
	{
		$sql = trim($sql);
		if (!preg_match('#^(INSERT INTO|UPDATE|TRUNCATE|DELETE FROM)\s+(.*?)\s+#', $sql, $match)) {
			return;
		}

		$table = $match[2];
		if (!isset($this->cache_tables[$table])) {
			return;
		}

		$cache = App::getCache('common', false);
		if (!$cache) {
			return;
		}

		App::getEntityRepository($this->cache_tables[$table])->invalidateFromQuery($sql);
	}

	public function stopQuery()
	{

	}
}
