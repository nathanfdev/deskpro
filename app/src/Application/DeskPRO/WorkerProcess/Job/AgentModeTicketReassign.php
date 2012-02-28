<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\Mail\QueueProcessor\Database as DatabaseQueueProcessor;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;
use Application\DeskPRO\Mail\Transport\DelegatingTransport;

/**
 * When an agent enters vacation mode or is deleted, we have to re-assign their awaiting_agent tickets
 * to unassigned. This does them in batches.
 *
 * We cant just do it in mysql because we need the proper logs to be generated.
 */
class AgentModeTicketReassign extends AbstractJob
{
	const DEFAULT_INTERVAL = 300;

	public function run()
	{
		$max = 1000;

		#------------------------------
		# Vacation mode
		#------------------------------

		$agent_ids = App::getDb()->fetchAllCol("SELECT id FROM people WHERE is_agent = 1 AND is_vacation_mode = 1");

		if ($agent_ids) {
			$tickets = App::getOrm()->createQuery("
				SELECT t
				FROM DeskPRO:Ticket
				WHERE t.status = 'awaiting_agent' AND t.agent IN (?)
				ORDER BY t.id DESC
			", array($agent_ids))->setMaxResults($max)->execute();

			foreach ($tickets as $t) {
				$t->agent = null;

				App::getDb()->beginTransaction();

				try {
					App::getOrm()->persist($t);
					App::getOrm()->flush();
					App::getDb()->commit();
				} catch (\Exception $e) {
					App::getDb()->rollback();
					throw $e;
				}
			}

			$max -= count($tickets);
		}

		#------------------------------
		# Deleted
		#------------------------------

		$agent_ids = App::getDb()->fetchAllCol("SELECT id FROM people WHERE is_agent = 1 AND is_deleted = 1");

		if ($max && $agent_ids) {

			$tickets = App::getOrm()->createQuery("
				SELECT t
				FROM DeskPRO:Ticket
				WHERE t.agent IN (?)
				ORDER BY t.id DESC
			", array($agent_ids))->setMaxResults($max)->execute();

			foreach ($tickets as $t) {
				$t->agent = null;

				App::getDb()->beginTransaction();

				try {
					App::getOrm()->persist($t);
					App::getOrm()->flush();
					App::getDb()->commit();
				} catch (\Exception $e) {
					App::getDb()->rollback();
					throw $e;
				}
			}
		}
	}
}
