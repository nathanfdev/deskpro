<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage DBAL
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
