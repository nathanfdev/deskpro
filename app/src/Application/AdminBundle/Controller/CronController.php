<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

		return $this->render("AdminBundle:Cron:list.html.twig", array(
			'jobs' => $jobs,
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
