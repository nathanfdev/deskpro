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

		#------------------------------
		# Binary paths
		#------------------------------

		$binary_paths = array(
			'php' => dp_get_config('php_path'),
			'mysql' => dp_get_config('mysql_path'),
			'mysqldump' => dp_get_config('mysqldump_path')
		);

		#------------------------------
		# Web PHP
		#------------------------------

		$web_php = array();
		$web_php['php_config'] = array(
			'version' => phpversion(),
			'memory_limit' => \Orb\Util\Env::getMemoryLimit(),
			'memory_limit_real' => DP_REAL_MEMSIZE,
			'error_log' => ini_get('error_log'),
			'error_log_real' => DP_REAL_ERROR_LOG,
		);

		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();
		preg_match('#<body.*?>(.*?)</body>#ms', $phpinfo, $m);

		if (isset($m[1])) {
			$phpinfo = $m[1];
		}

		$web_php['phpinfo'] = $phpinfo;
		$web_php['ini_path'] = \Orb\Util\Env::getPhpIniPathFromInfo($web_php['phpinfo']);
		$web_php['effective_max_upload'] = \Orb\Util\Env::getEffectiveMaxUploadSize();

		#------------------------------
		# CLI PHP
		#------------------------------

		$cli_php = array('phpinfo' => null, 'php_config' => null);

		if (file_exists(dp_get_data_dir() .'/cli-phpinfo.html')) {
			$phpinfo = file_get_contents(dp_get_data_dir() .'/cli-phpinfo.html');
			$cli_php['ini_path'] = \Orb\Util\Env::getPhpIniPathFromInfo($phpinfo);

			if (strpos($phpinfo, '<body') === false) {
				$phpinfo = '<code>' . nl2br(htmlspecialchars($phpinfo)) . '</code>';
			} else {
				preg_match('#<body.*?>(.*?)</body>#ms', $phpinfo, $m);

				if (isset($m[1])) {
					$phpinfo = $m[1];
				}
			}

			$cli_php['phpinfo'] = $phpinfo;
		}

		if (file_exists(dp_get_data_dir() .'/cli-server-reqs-check.dat')) {
			$data = file_get_contents(dp_get_data_dir() .'/cli-server-reqs-check.dat');
			$data = @unserialize($data);

			$cli_php['php_config'] = $data;

			if (isset($cli_php['php_config']['memory_limit_real'])) {
				if ($cli_php['php_config']['memory_limit_real'] == -1) {
					$cli_php['effective_max_upload'] = -1;
				} else {
					$cli_php['effective_max_upload'] = $cli_php['php_config']['memory_limit_real'] / 3;
				}
			}
		}

		$has_apc = false;
		if (function_exists('apc_store') && ini_get('apc.enabled')) {
			$has_apc = true;
		}

		return $this->render('AdminBundle:Server:phpinfo.html.twig', array(
			'binary_paths' => $binary_paths,
			'web_php'      => $web_php,
			'cli_php'      => $cli_php,
			'config_hash'  => $config_hash,
			'has_apc'      => $has_apc,
		));
	}

	############################################################################
	# server-checks
	############################################################################

	public function serverChecksAction()
	{
		#------------------------------
		# Web checks
		#------------------------------

		$server_check = new \Application\InstallBundle\Install\ServerChecks();
		$server_check->checkServer();

		$is_fatal = $server_check->hasFatalErrors();

		$ini_path = '';
		if ($server_check->hasErrors()) {
			$ini_path = \Orb\Util\Env::getPhpIniPath();
		}

		$table_vars = array(
			'errors' => $server_check->getErrors(),
			'is_fatal' => $is_fatal,
			'has_db_checks' => false,
			'db_config' => App::getConfig('db'),
			'ini_path' => $ini_path,
		);

		$table = $this->renderView('AdminBundle:Server:server-checks-table.html.php', $table_vars);
		$vars['web_table'] = $table;

		#------------------------------
		# CLI checks
		#------------------------------

		if (file_exists(dp_get_data_dir() .'/cli-server-reqs-check.dat')) {
			$data = file_get_contents(dp_get_data_dir() .'/cli-server-reqs-check.dat');
			$data = @unserialize($data);

			$phpinfo = '';
			if (file_exists(dp_get_data_dir() .'/cli-phpinfo.html')) {
				$phpinfo = file_get_contents(dp_get_data_dir() .'/cli-phpinfo.html');
			}

			$is_fatal = in_array('fatal', $data['checks']);

			$table_vars = array(
				'errors' => $data['checks'],
				'is_fatal' => $is_fatal,
				'has_db_checks' => false,
				'ini_path' => \Orb\Util\Env::getPhpIniPathFromInfo($phpinfo),
			);

			$table = $this->renderView('AdminBundle:Server:server-checks-table.html.php', $table_vars);
			$vars['cli_table'] = $table;
		}

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
		$config_hash = md5_file(DP_CONFIG_FILE);

		$log_reader = new \Application\DeskPRO\Log\ErrorLog\ErrorLogReader(dp_get_log_dir() . '/error.log');

		return $this->render('AdminBundle:Server:error-logs.html.twig', array(
			'config_hash' => $config_hash,
			'logs' => $log_reader
		));
	}

	public function viewErrorLogAction($log_id)
	{
		$log_reader = new \Application\DeskPRO\Log\ErrorLog\ErrorLogReader(dp_get_log_dir() . '/error.log');
		$log_reader->enableRawLog();
		$log_reader->setIdFilter($log_id);

		$log = $log_reader->next();

		if (!$log) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		return $this->render('AdminBundle:Server:error-view.html.twig', array(
			'log' => $log
		));
	}

	public function errorLogsClearAllAction()
	{
		$this->ensureRequestToken('clear_error_logs', 'x');
		@file_put_contents(dp_get_log_dir() . '/error.log', '');
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

		$php_vars['memory_limit'] = \Orb\Util\Env::getMemoryLimit();
		$php_vars['memory_limit_real'] = DP_REAL_MEMSIZE;

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
