<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;

class CleanupQuarterHourly extends AbstractJob
{
	const DEFAULT_INTERVAL = 900;

	public function run()
	{
		$this->doRun();
		App::getDb()->setIsolationDefault();
	}

	private function doRun()
	{
		#------------------------------
		# Page cache
		#------------------------------

		$cache = new \Application\DeskPRO\CacheInvalidator\UserPageCache();
		$cache->cleanup();

		#------------------------------
		# Old API logs
		#------------------------------

		App::$container->getEm()->getRepository('DeskPRO:ApiKeyLog')->cleanup();

		#------------------------------
		# Update table counts
		#------------------------------

		$counts = array();
		$counts['tickets']                    = App::getDb()->fetchColumn("SELECT COUNT(*) FROM `tickets`");
		$counts['tickets.resolved']           = App::getDb()->fetchColumn("SELECT COUNT(*) FROM `tickets_search_active` WHERE `status` = 'resolved'");
		$counts['tickets.archive_validating'] = App::getDb()->fetchColumn("SELECT COUNT(*) FROM `tickets` WHERE `status` = 'hidden' AND `hidden_status` = 'validating'");
		$counts['tickets.archive_spam']       = App::getDb()->fetchColumn("SELECT COUNT(*) FROM `tickets` WHERE `status` = 'hidden' AND `hidden_status` = 'spam'");
		$counts['tickets.archive_deleted']    = App::getDb()->fetchColumn("SELECT COUNT(*) FROM `tickets` WHERE `status` = 'hidden' AND `hidden_status` = 'deleted'");
		$counts['tickets.archive_archived']   = App::getDb()->fetchColumn("SELECT COUNT(*) FROM `tickets` WHERE `status` = 'archived'");
		$counts['people']                     = App::getDb()->fetchColumn("SELECT COUNT(*) FROM `people`");

		foreach ($counts as $k => $v) {
			App::getDb()->replace('settings', array(
				'name'  => "core_tablecounts.$k",
				'value' => (int)$v
			));
		}

		// Fetch in agent context
		$filters = App::getOrm()->createQuery("
			SELECT f
			FROM DeskPRO:TicketFilter f
			WHERE f.sys_name LIKE 'archive_%' AND f.sys_name != 'archive_resolved' AND f.sys_name != 'archive_awaiting_user'
		")->execute();

		$inserts = array();

		foreach (App::getContainer()->getAgentData()->getAgents() as $agent) {
			$agent->loadHelper('Agent');
			$agent->loadHelper('AgentTeam');
			$agent->loadHelper('AgentPermissions');
			$agent->loadHelper('PermissionsManager');
			$agent->loadHelper('HelpMessages');
			$agent->loadHelper('AgentPrefs');

			foreach ($filters as $filter) {
				/** @var \Application\DeskPRO\Entity\TicketFilter $filter*/
				$searcher = $filter->getSearcher();
				$searcher->setPersonContext($agent);

				$count = $searcher->getCount();

				$inserts[] = array(
					'person_id'   => $agent->id,
					'name'        => "ticket_counts.{$filter->sys_name}",
					'value_str'   => $count,
					'value_array' => null,
					'date_expire' => null
				);
			}
		}

		if ($inserts) {
			App::getDb()->executeUpdate("DELETE FROM people_prefs WHERE name LIKE 'ticket_counts.%'");
			App::getDb()->batchInsert('people_prefs', $inserts, true);
		}
	}
}
