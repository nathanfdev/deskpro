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

		$count_online_users = $this->db->fetchColumn("
			SELECT COUNT(DISTINCT sessions.visitor_id)
			FROM sessions
			LEFT JOIN people ON (people.id = sessions.person_id)
			WHERE sessions.date_last > '2012-09-04 10:45:09' AND sessions.is_helpdesk = 1 AND (people.id IS NULL OR people.is_agent = 0)
		", array(date('Y-m-d H:i:s', time() - App::getSetting('core.sessions_lifetime'))));

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
		return $this->createJsonResponse(array('okay' => 1));
	}
}
