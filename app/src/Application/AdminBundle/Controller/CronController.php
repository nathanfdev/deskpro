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
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Displays cron jobs
 */
class CronController extends AbstractController
{
	public function listAction()
	{
		$jobs = $this->em->createQuery("
			SELECT j
			FROM DeskPRO:WorkerJob j
			ORDER BY j.interval ASC
		")->execute();

		$last_run = $this->container->getSetting('core.last_cron_run');
		if (!$last_run) $last_run = 0;

		$time_since_run = time() - $last_run;
		$is_problem = false;
		if ($time_since_run > 301) {
			$is_problem = true;
		}

		return $this->render("AdminBundle:Cron:list.html.twig", array(
			'jobs' => $jobs,
			'last_run' => $last_run,
			'time_since_run' => $time_since_run,
			'is_problem' => $is_problem,
		));
	}

	public function logsAction()
	{
		$logs = $this->db->fetchAll("
			SELECT log_name, session_name, message
			FROM log_items
			WHERE log_name LIKE 'worker_job.%'
			ORDER BY id DESC
			LIMIT 1500
		");

		return $this->render('AdminBundle:Cron:logs.html.twig', array(
			'logs' => $logs,
		));
	}

	public function clearLogsAction()
	{
		$this->ensureRequestToken('clear_cron_logs', 'x');
		$this->db->exec("DELETE FROM log_items WHERE log_name LIKE 'worker_job.%'");

		return $this->redirectRoute('admin_server_cron');
	}
}
