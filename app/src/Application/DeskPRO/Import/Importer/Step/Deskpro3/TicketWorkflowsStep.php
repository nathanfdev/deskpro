<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Entity\TicketWorkflow;

class TicketWorkflowsStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Ticket Workflows';
	}

	public function run($page = 1)
	{
		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM ticket_workflow");
		$this->logMessage(sprintf("Importing %d ticket workflows", $count));
		if (!$count) {
			return;
		}

		$workflows = $this->getOldDb()->fetchAll("SELECT * FROM ticket_workflow ORDER BY id ASC");

		$start_time = microtime(true);

		$this->getDb()->beginTransaction();

		try {
			foreach ($workflows as $work) {
				$this->processWorkflow($work);
			}

			if ($workflows) {
				$this->getDb()->replace('settings', array(
					'name' => 'core.use_ticket_workflow',
					'groupname' => 'core',
					'value' => 1,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				));
			}

			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}

		$end_time = microtime(true);
		$this->logMessage(sprintf("Done all workflows. Took %.3f seconds.", $end_time-$start_time));
	}

	protected function processWorkflow(array $work)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('ticket_workflow', $work['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$work['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_work = new TicketWorkflow();
		$new_work->title = $work['name'];
		$new_work->display_order = $work['displayorder'];

		$this->getEm()->persist($new_work);
		$this->getEm()->flush();

		$this->saveMappedId('ticket_workflow', $work['id'], $new_work->id);
	}
}
