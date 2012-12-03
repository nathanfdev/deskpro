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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\Mail\QueueProcessor\Database as DatabaseQueueProcessor;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;
use Application\DeskPRO\Entity\TicketTrigger;

use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;

/**
 * Will send daily task due reminders
 */
class TaskReminders extends AbstractJob
{
	const DEFAULT_INTERVAL = 3600;

	public function run()
	{
		$date = date('Y-m-d');
		$last_run = App::getSetting('core.last_task_reminder_date');
		if ($date === $last_run) {
			$this->getLogger()->logInfo("Already run for today ($date). No action taken.");
			return;
		}

		$em = App::getOrm();
		$emails = 0;
		$alerts = 0;

		$tasks = App::getEntityRepository('DeskPRO:Task')->getTasksDueOnDate($date);
		if ($tasks) {
			$online_ids = App::getEntityRepository('DeskPRO:Session')->getAvailableAgentIds();

			foreach ($tasks AS $task) {
				if ($task->assigned_agent_team) {
					$agents = $task->assigned_agent_team->members;
				} else {
					$agent = $task->assigned_agent;
					if (!$agent) {
						$agent = $task->person;
					}
					$agents = array($agent);
				}

				foreach ($agents AS $agent) {
					if (in_array($agent->id, $online_ids) && $agent->getPref('agent_notif.task_due.alert')) {
						$tpl_line = App::getTemplating()->render('AgentBundle:Task:notify-row-reminder.html.twig', array(
							'task' => $task,
							'person' => $agent
						));

						$cm = new \Application\DeskPRO\Entity\ClientMessage();
						$cm->fromArray(array(
							'channel' => 'agent-notify.tasks',
							'data' => array('row' => $tpl_line),
							'for_person'        => $agent,
							'created_by_client' => 'sys'
						));
						$em->persist($cm);

						$alerts++;
					}

					if ($agent->getPref('agent_notif.task_due.email')) {
						$message = App::getMailer()->createMessage();
						$message->setTemplate('DeskPRO:emails_agent:task-due-reminder.html.twig', array(
							'task' => $task,
							'person' => $agent
						));
						$message->setToPerson($agent);
						$message->setFrom(App::getSetting('core.default_from_email'), App::getSetting('core.deskpro_name'));
						App::getMailer()->send($message);

						$emails++;
					}
				}

				$em->flush();
			}
		}

		if ($emails || $alerts) {
			$this->getLogger()->logInfo("Reminders for $date sent (alerts: $alerts, emails: $emails)");
		}

		App::getContainer()->getSettingsHandler()->setSetting('core.last_task_reminder_date', $date);
	}
}