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
*/

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use DeskPRO\Kernel\License;

class MainController extends AbstractController
{
    public function indexAction()
	{
		$server_check = new \Application\InstallBundle\Install\ServerChecks();
		$server_check->checkServer();
		$server_check->checkDatabase(null, true);

		$notice_items = $server_check->getNonFatalErrors();

		$agents_online_ids = $this->em->getRepository('DeskPRO:Session')->getAvailableAgentIds();
		$online_agents = array();
		foreach ($agents_online_ids as $aid) {
			$online_agents[] = $this->em->getRepository('DeskPRO:Person')->getAgent($aid);
		}

		$count_online_users = $this->em->getRepository('DeskPRO:Session')->countOnlineUsers();

		$stats = array();
		$today = $this->person->getDateTime();
		$today->setTime(0,0,0);
		$today = \Orb\Util\Dates::convertToUtcDateTime($today);
		$today = $today->format('Y-m-d H:i:s');

		$stats['created_today']  = $this->db->fetchColumn("SELECT COUNT(*) FROM tickets WHERE date_created > ?", array($today));
		$stats['resolved_today'] = $this->db->fetchColumn("SELECT COUNT(*) FROM tickets WHERE date_resolved > ?", array($today));
		$stats['awaiting_agent'] = $this->db->fetchColumn("SELECT COUNT(*) FROM tickets WHERE status = 'awaiting_agent'");

		$err_reader = new \Application\DeskPRO\Log\ErrorLog\ErrorLogReader(dp_get_log_dir() . '/error.log');
		$err_reader->enableCountMode();
		$error_count = $err_reader->count();

		$last_run = $this->container->getSetting('core.last_cron_run');
		if (!$last_run) $last_run = 0;

		$time_since_run = time() - $last_run;
		$is_cron_crash = false;
		$cron_running_time = '';
		if ($time_since_run > 301) {
			$is_cron_crash = true;
			$cron_running_time = \Orb\Util\Dates::secsToReadable(time() - $last_run, 5);
		}

		$last_login = $this->em->getRepository('DeskPRO:LoginLog')->getLast($this->person);

		$tasks = $this->em->getRepository('DeskPRO:TaskQueue')->getPendingTasks(0);
		$show_task_status = count($tasks) > 0;

		return $this->render('AdminBundle:Main:index.html.twig', array(
			'lic'                => License::getLicense(),
			'notice_items'       => $notice_items,
			'online_agents'      => $online_agents,
			'count_online_users' => $count_online_users,
			'stats'              => $stats,
			'error_count'        => $error_count,
			'is_cron_crash'      => $is_cron_crash,
			'cron_running_time'  => $cron_running_time,
			'last_login'         => $last_login,
			'show_task_status'   => $show_task_status
		));
	}

	public function dashVersionInfoAction()
	{
		try {
			$version_info = \Application\DeskPRO\Service\LicenseService::compareVersion();
		} catch (\Exception $e) {
			$version_info = null;
		}

		return $this->render('AdminBundle:Main:part-version-info.html.twig', array(
			'version_info' => $version_info
		));
	}

	public function acceptTempUploadAction()
	{
		$file = $this->request->files->get('file-upload');

		$accept = $this->container->getAttachmentAccepter();

		$error = $accept->getError($file, 'agent');
		if (!$error && $this->in->getBool('is_image')) {
			$set = new \Application\DeskPRO\Attachments\RestrictionSet();
			$set->setAllowedExts(array('gif', 'png', 'jpg', 'jpeg'));
			$accept->addRestrictionSet('only_images', $set);
			$error = $accept->getError($file, 'only_images');
		}
		if ($error) {
			$error['error'] = $this->container->getTranslator()->phrase('agent.general.attach_error_' . $error['error_code'], $error);
			return $this->createJsonResponse(array($error));
		}

		$desc = App::getApi('filestorage')->createRandomPath();

		$desc->write(file_get_contents($file->getRealPath()), array(
			'content_type' => $file->getClientMimeType(),
			'filename' => $file->getClientOriginalName()
		));

		$blob_id = $desc->getPath();
		$blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);

		if ($this->in->getString('attach_to_object')) {
			switch ($this->in->getString('attach_to_object')) {
				case 'article':
					$article = $this->em->find('DeskPRO:Article', $this->in->getUint('object_id'));

					$attach = new \Application\DeskPRO\Entity\ArticleAttachment();
					$attach['blob'] = $blob;
					$attach['person'] = $this->person;

					$article->addAttachment($attach);

					$this->em->persist($article);
					$this->em->flush();

					break;
			}
		}

		return $this->createJsonResponse(array(array(
			'blob_id' => $blob['id'],
			'blob_auth' => $blob->authcode,
			'blob_auth_id' => $blob->id . '-' . $blob->authcode,
			'download_url' => $blob->getDownloadUrl(true),
			'filename' => $blob['filename'],
			'filesize_readable' => $blob->getReadableFilesize()
		)));
	}

	public function skipSetupStepAction()
	{
		$this->setup_guide->skipNextTask();
		return $this->redirectRoute('admin');
	}

	public function sessionPingAction()
	{
		return $this->createJsonResponse(array(
			'okay' => 1,
			'request_token' => App::getSession()->getEntity()->generateSecurityToken('request_token', 10800)
		));
	}

	public function checkTaskQueueAction($task_queue_id = 0)
	{
		if ($task_queue_id) {
			$task = $this->em->getRepository('DeskPRO:TaskQueue')->find($task_queue_id);
			if (!$task) {
				return $this->createJsonResponse(array('exists' => false));
			} else {
				$runner = $task->getRunner();

				return $this->createJsonResponse(array(
					'exists' => true,
					'title' => $runner->getTitle(),
					'status' => $task->status,
					'run_status' => $task->run_status,
					'error_text' => $task->error_text
				));
			}
		} else {
			$tasks = $this->em->getRepository('DeskPRO:TaskQueue')->getPendingTasks();
			return $this->_getMultipleTaskQueueStatusResponse($tasks);
		}
	}

	public function checkTaskQueueGroupAction($task_group)
	{
		$tasks = $this->em->getRepository('DeskPRO:TaskQueue')->getTasksInGroup($task_group);
		return $this->_getMultipleTaskQueueStatusResponse($tasks);
	}

	protected function _getMultipleTaskQueueStatusResponse($tasks)
	{
		if (!count($tasks)) {
			return $this->createJsonResponse(array('count' => 0));
		} else {
			$task = reset($tasks);
			$runner = $task->getRunner();

			return $this->createJsonResponse(array(
				'count' => count($tasks),
				'count_waiting' => $this->em->getRepository('DeskPRO:TaskQueue')->countTasksBefore($task),
				'title' => $runner->getTitle(),
				'status' => $task->status,
				'run_status' => $task->run_status,
				'error_text' => $task->error_text
			));
		}
	}
}
