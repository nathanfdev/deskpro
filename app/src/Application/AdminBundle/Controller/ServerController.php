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
use Orb\Util\Numbers;

/**
 * Server info
 */
class ServerController extends AbstractController
{
	############################################################################
	# phpinfo
	############################################################################

	public function phpinfoAction()
	{
		$config_hash = md5_file(DP_CONFIG_FILE);
		$php_config = array(
			'version' => phpversion(),
			'memory_limit' => \Orb\Util\Env::getMemoryLimit(),
			'error_log' => ini_get('error_log'),
		);

		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();

		preg_match('#<body.*?>(.*?)</body>#ms', $phpinfo, $m);

		if (isset($m[1])) {
			$phpinfo = $m[1];
		}

		return $this->render('AdminBundle:Server:phpinfo.html.twig', array(
			'phpinfo' => $phpinfo,
			'config_hash' => $config_hash,
			'php_config' => $php_config,
		));
	}

	############################################################################
	# server-checks
	############################################################################

	public function serverChecksAction()
	{
		$server_check = new \Application\InstallBundle\Install\ServerChecks();
		$server_check->checkServer();

		$is_fatal = $server_check->hasFatalErrors();
		$has_db_checks = false;

		if (!$is_fatal) {
			$has_db_checks = true;
			$server_check->checkDatabase(App::getConfig('db'), false);
		}

		$is_fatal = $server_check->hasFatalErrors();

		$ini_path = '';
		if ($server_check->hasErrors()) {
			$ini_path = \Orb\Util\Env::getPhpIniPath();
		}

		$vars = array(
			'errors' => $server_check->getErrors(),
			'is_fatal' => $is_fatal,
			'has_db_checks' => $has_db_checks,
			'db_config' => App::getConfig('db'),
			'ini_path' => $ini_path,
		);

		$table = $this->renderView('AdminBundle:Server:server-checks-table.html.php', $vars);
		$vars['table'] = $table;

		return $this->render('AdminBundle:Server:server-checks.html.twig', $vars);
	}


	############################################################################
	# file-checks
	############################################################################

	public function fileChecksAction()
	{
		$verify = new \Application\DeskPRO\Distribution\VerifyChecksums();
		$count  = $verify->countChunks();

		return $this->render('AdminBundle:Server:file-checks.html.twig', array(
			'count' => $count,
		));
	}

	public function fileChecksDoAction($batch = 0)
	{
		$verify = new \Application\DeskPRO\Distribution\VerifyChecksums();
		$results = $verify->compareChunk($batch);

		return $this->render('AdminBundle:Server:file-checks-do.html.twig', array(
			'results' => $results,
			'batch' => $batch
		));
	}


	############################################################################
	# mysqlinfo
	############################################################################

	public function mysqlinfoAction()
	{
		try {
			$mysqlinfo = $this->db->fetchAllKeyValue("SHOW VARIABLES", array(), 0, 1);
		} catch (\Exception $e) {
			$mysqlinfo = null;
		}

		return $this->render('AdminBundle:Server:mysqlinfo.html.twig', array(
			'mysqlinfo' => $mysqlinfo
		));
	}


	############################################################################
	# mysqlstatus
	############################################################################

	public function mysqlstatusAction()
	{
		try {
			$mysqlprocs = $this->db->fetchAll("SHOW PROCESSLIST");
		} catch (\Exception $e) {
			$mysqlprocs = null;
		}

		try {
			$mysqlstatus = $this->db->fetchAllKeyValue("SHOW STATUS", array(), 0, 1);
		} catch (\Exception $e) {
			$mysqlstatus = null;
		}

		return $this->render('AdminBundle:Server:mysqlstatus.html.twig', array(
			'mysqlprocs' => $mysqlprocs,
			'mysqlstatus' => $mysqlstatus
		));
	}


	############################################################################
	# error-logs
	############################################################################

	public function errorLogsAction()
	{
		$page = max(1, $this->in->getUint('p'));

		$logs_count = $this->em->getRepository('DeskPRO:LogItem')->getErrorLogsCount($page);
		$logs = $this->em->getRepository('DeskPRO:LogItem')->getErrorLogs($page, 25);
		$pagination = Numbers::getPaginationPages($logs_count, $page, 25, 5);

		return $this->render('AdminBundle:Server:error-logs.html.twig', array(
			'logs_count' => $logs_count,
			'logs' => $logs,
			'pagination' => $pagination
		));
	}

	public function viewSnAction($log_sn)
	{
		$log_sn = trim(preg_replace('#^SN#', '', $log_sn));

		$log = $this->em->getRepository('DeskPRO:LogItem')->findBySn($log_sn);
		if (!$log) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_log");
		}

		$data_structure = '';
		if ($log['data']) {
			$data_structure = print_r($log['data'], true);
		}

		return $this->render('AdminBundle:Server:error-view.html.twig', array(
			'log' => $log,
			'data_structure' => $data_structure
		));
	}

	public function viewAction($log_id)
	{
		$log = $this->em->getRepository('DeskPRO:LogItem')->find($log_id);
		if (!$log) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_log");
		}

		$data_structure = '';
		if ($log['data']) {
			$data_structure = print_r($log['data'], true);
		}

		return $this->render('AdminBundle:Server:error-view.html.twig', array(
			'log' => $log,
			'data_structure' => $data_structure
		));
	}

	public function errorLogsClearAllAction()
	{
		$this->ensureRequestToken('clear_error_logs', 'x');
		$this->db->exec("DELETE FROM log_items WHERE log_name = 'error_log'");

		return $this->redirectRoute('admin_server_error_logs');
	}

	############################################################################
	# attachments
	############################################################################

	public function attachmentsAction()
	{
		$php_vars = array();

		foreach (array('file_uploads', 'upload_tmp_dir', 'upload_max_filesize', 'post_max_size') as $var) {
			$php_vars[$var] = @ini_get($var);
		}

		$failed = false;
		$attach = false;
		$has_uploaded = false;
		if ($this->in->getBool('test')) {
			$has_uploaded = true;
			$file = $this->request->files->get('file');
			$accept = $this->container->getAttachmentAccepter();

			$error = $accept->getError($file, 'agent');
			if ($error) {
				$failed = $this->container->getTranslator()->phrase('agent.general.attach_error_' . $error['error_code'], $error);
			} else {
				$attach = $accept->accept($file);
			}
		}

		$filestorage_path = $this->container->getBlobDir();
		$use_fs = ($this->container->getSetting('core.filestorage_method') == 'fs');

		$moving_id = $this->container->getSetting('core.filesystem_move_from_id');
		if ($moving_id) {
			if ($moving_id < 1) {
				$count_done = 0;
			} else {
				$count_done = $this->container->getDb()->fetchColumn("SELECT COUNT(*) FROM blobs WHERE id < ?", array($moving_id));
			}
			$count_todo = $this->container->getDb()->fetchColumn("SELECT COUNT(*) FROM blobs", array($moving_id));
			if (!$count_todo) {
				$count_todo = 1;
			}
			$count_left = $count_todo - $count_done;
			$count_perc = floor(($count_done / $count_todo) * 100);
		} else {
			$count_done = $count_todo = $count_left = $count_perc = 0;

			$count_todo = $this->container->getDb()->fetchColumn("SELECT COUNT(*) FROM blobs");
		}

		$total_size = $this->container->getDb()->fetchColumn("SELECT SUM(filesize) FROM blobs");
		$total_size_readable = Numbers::filesizeDisplay($total_size);

		$php_ini = \Orb\Util\Env::getPhpIniPath();
		$effective_max = \Orb\Util\Env::getEffectiveMaxUploadSize();

		return $this->render('AdminBundle:Server:attachments.html.twig', array(
			'php_vars' => $php_vars,
			'has_uploaded' => $has_uploaded,
			'attach' => $attach,
			'failed' => $failed,
			'php_ini' => $php_ini,
			'effective_max' => $effective_max,
			'effective_max_display' => Numbers::filesizeDisplay($effective_max),

			'filestorage_path' => $filestorage_path,
			'use_fs' => $use_fs,
			'moving_id' => $moving_id,
			'count_done' => $count_done,
			'count_todo' => $count_todo,
			'count_left' => $count_left,
			'count_perc' => $count_perc,
			'total_size' => $total_size,
			'total_size_readable' => $total_size_readable,
		));
	}

	public function attachmentsSwitchAction()
	{
		$this->ensureRequestToken();

		$use_fs = ($this->container->getSetting('core.filestorage_method') == 'fs');
		if ($use_fs) {
			$this->container->getEm()->getRepository('DeskPRO:Setting')->updateSetting('core.filestorage_method', 'db');
		} else {
			$this->container->getEm()->getRepository('DeskPRO:Setting')->updateSetting('core.filestorage_method', 'fs');
		}

		$this->container->getEm()->getRepository('DeskPRO:Setting')->updateSetting('core.filesystem_move_from_id', '-1');

		return $this->redirectRoute('admin_server_attach');
	}
}
