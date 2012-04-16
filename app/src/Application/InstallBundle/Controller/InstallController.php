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

namespace Application\InstallBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;

/**
 * Installation
 */
class InstallController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
	/**
	 * @return \Orb\Log\Logger
	 */
	public function getLogger($reset = false)
	{
		static $logger = null;

		if ($logger === null) {
			$logger = new \Orb\Log\Logger();

			try {
				$wr = new \Orb\Log\Writer\Stream($this->container->getLogDir() . '/install.log', $reset ? 'w' : 'a');
				$logger->addWriter($wr);
			} catch (\Exception $e) {}

			$GLOBALS['DP_ERR_LOGGER'] = $logger;
		}

		return $logger;
	}

	public function ensureNotInstalled()
	{
		try {
			$this->getDb()->connect();

			$installed = $this->getDb()->fetchColumn("SELECT value FROM settings WHERE name = ?", array('core.install_timestamp'));
			if ($installed) {
				return false;
			}

		} catch (\Exception $e) {
			return true;
		}

		return true;
	}

	###############################################################################
	# index
	###############################################################################

	public function indexAction()
	{
		if (!file_exists(DP_CONFIG_FILE) && is_writable(dirname(DP_CONFIG_FILE))) {
			return $this->redirect($this->generateUrl('install_configedit'));
		}

		$can_write_config = false;
		if (file_exists(DP_CONFIG_FILE) && is_writable(DP_CONFIG_FILE)) {
			$can_write_config = true;
		}

		$this->getLogger(true)->log('Install::index', 'debug');

		$server_check = new \Application\InstallBundle\Install\ServerChecks();
		$server_check->setLogger($this->getLogger());
		$server_check->checkServer();

		$is_fatal = $server_check->hasFatalErrors();

		$has_config = false;
		$has_db_checks = false;

		if (!$is_fatal) {
			$did_create_db = false;
			$has_db_checks = true;
			if (file_exists(DP_CONFIG_FILE)) {
				$has_config = true;

				try {
					App::getDb()->connect();
				} catch (\PDOException $e) {
					if ($e->getCode() == '1049') {

						// Attempt to create an empty database
						try {
							global $DP_CONFIG;
							$dbh = new \PDO("mysql:host={$DP_CONFIG['db']['host']}", $DP_CONFIG['db']['user'], $DP_CONFIG['db']['password']);
							$dbh->exec("CREATE DATABASE `{$DP_CONFIG['db']['dbname']}`");
							$did_create_db = true;
						} catch (\Exception $e) {
							$did_create_db = false;
						}
					}
				}

				$server_check->checkDatabase(App::getConfig('db'));

				if (!$server_check->hasDbErrors()) {
					try {
						$installed = $this->getDb()->fetchColumn("SELECT value FROM settings WHERE name = ?", array('core.install_timestamp'));
					} catch (\Exception $e) { $installed = false; }
					if ($installed) {
						// Already installed, go to homepage
						return $this->redirect($this->container->getRequest()->getBaseUrl());
					}
				}
			}
		}

		$is_fatal = $server_check->hasFatalErrors();

		$logs_dir_info = $this->container->getKernel()->getUserLogDir();
		$logs_dir_info = str_replace(DP_WEB_ROOT, '', $logs_dir_info);

		if (!isset($_POST['stats_opt_out'])) {
			$stats_fetcher = new \Application\InstallBundle\Data\ServerStats($this->getDb());
			$stats = $stats_fetcher->getStats();
			$stats['server_check_errors'] = $server_check->getErrors();

			$data = array('stats' => $stats);
			$data['is_error'] = $server_check->hasFatalErrors();
			$data['source_type'] = 'install.web';
			\Application\DeskPRO\Service\ErrorReporter::sendReport('report-stats', $data, 10);
		}

		$ini_path = '';
		if ($server_check->hasErrors()) {
			$ini_path = \Orb\Util\Env::getPhpIniPath();
		}

		return $this->render('InstallBundle:Install:index.html.php', array(
			'can_write_config' => $can_write_config,
			'errors' => $server_check->getErrors(),
			'has_config' => $has_config,
			'is_fatal' => $is_fatal,
			'has_db_checks' => $has_db_checks,
			'db_config' => App::getConfig('db'),
			'logs_dir_info' => $logs_dir_info,
			'ini_path' => $ini_path,
			'did_create_db' => $did_create_db,
		));
	}

	###############################################################################
	# config-editor
	###############################################################################

	public function configEditorAction()
	{
		if ( (!file_exists(DP_CONFIG_FILE) && !is_writable(dirname(DP_CONFIG_FILE))) || (file_exists(DP_CONFIG_FILE) && !is_writable(DP_CONFIG_FILE))) {
			return $this->redirect($this->generateUrl('install_checks'));
		}
		$exist = array(
			'DP_DATABASE_HOST'     => '',
			'DP_DATABASE_USER'     => '',
			'DP_DATABASE_PASSWORD' => '',
			'DP_DATABASE_NAME'     => ''
		);

		if (isset($_REQUEST['process'])) {
			$file = file_get_contents(DP_WEB_ROOT.'/config.new.php');
			foreach (array_keys($exist) as $k) {
				$value = !empty($_REQUEST[$k]) ? $_REQUEST[$k] : '';
				$file = preg_replace("#^define\('$k'.*?$#m", "define('$k', '" . addslashes($value) . "');", $file);
			}

			file_put_contents(DP_CONFIG_FILE, $file);
			return $this->redirect($this->generateUrl('install_checks'));
		}

		if (file_exists(DP_CONFIG_FILE)) {
			@include(DP_CONFIG_FILE);
		}

		foreach ($exist as $k => &$v) {
			if (defined($k)) {
				$v = constant($k);
			}
		}

		return $this->render('InstallBundle:Install:config-editor.html.php', array(
			'exist' => $exist
		));
	}

	###############################################################################
	# license
	###############################################################################

	public function licenseAction()
	{
        try {
            if(count($this->getDoctrine()->getConnection()->fetchAll('SHOW TABLES'))) {
                return $this->redirect($this->generateUrl('install_install_data'));
            }
        }
        catch(\Exception $e) {}

		return $this->render('InstallBundle:Install:license.html.php', array(

		));
	}


	###############################################################################
	# verify-files
	###############################################################################

	public function verifyFilesAction()
	{
		if (!$this->ensureNotInstalled()) {
			return $this->redirect($this->generateUrl('install'));
		}

		$this->getLogger()->log('Install::verifyFiles', 'debug');

		if (!file_exists(DP_ROOT.'/sys/Resources/distro-checksums.php')) {
			$this->getLogger()->log('distro-checksums.php missing', 'debug');
			return $this->redirect($this->get('router')->generate('install_create_tables'));
		}

		$verify = new \Application\DeskPRO\Distribution\VerifyChecksums();
		$count  = $verify->countChunks();

		return $this->render('InstallBundle:Install:verify-files.html.php', array(
			'count' => $count,
		));
	}

	public function doVerifyFilesAction($batch = 0)
	{
		if (!$this->ensureNotInstalled()) {
			exit;
		}

		$verify = new \Application\DeskPRO\Distribution\VerifyChecksums();
		$results = $verify->compareChunk($batch);

		if (!empty($results['changed'])) {
			foreach ($results['changed'] as $f) {
				$this->getLogger()->log("[VerifyChecksums] Changed: " . $f, 'err');
			}
		}
		if (!empty($results['added'])) {
			foreach ($results['added'] as $f) {
				$this->getLogger()->log("[VerifyChecksums] Added: " . $f, 'err');
			}
		}
		if (!empty($results['removed'])) {
			foreach ($results['removed'] as $f) {
				$this->getLogger()->log("[VerifyChecksums] Removed: " . $f, 'err');
			}
		}

		return $this->render('InstallBundle:Install:verify-files-do.html.php', array(
			'results' => $results,
			'batch' => $batch
		));
	}

	###############################################################################
	# create-tables
	###############################################################################

	public function createTablesAction()
	{
		if (!$this->ensureNotInstalled()) {
			return $this->redirect($this->generateUrl('install'));
		}

		$this->getLogger()->log('Install::createTables', 'debug');

		$db = $this->getDb();
		$check = $db->fetchColumn("SHOW TABLES LIKE 'install_data'");

		if ($check != 'install_data') {
			try {
				$db->exec("
					CREATE TABLE `install_data` (
					  `build` varchar(30) NOT NULL,
					  `name` varchar(75) NOT NULL DEFAULT '',
					  `data` blob NOT NULL,
					  PRIMARY KEY (`build`,`name`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1
				");
			} catch (\Exception $e) {
				$this->getLogger()->log('Failed to craete install_data: ' . $e->getCode() . ' ' . $e->getMessage(), 'err');

				$html = deskpro_install_basic_error('There was a problem trying to create the first database table `install_data`: ' . $e->getCode() . ' ' . $e->getMessage());
				$res = new \Symfony\Component\HttpFoundation\Response($html);
				$res->headers->set('Content-Type', 'text/html');
				return $res;
			}

			$tableinfo = $db->fetchColumn("SHOW CREATE TABLE `install_data`", array(), 1);
			if (stripos($tableinfo, 'innodb') === false) {
				$this->getLogger()->log('install_data is not innodb', 'err');

				$html = deskpro_install_basic_error('Your database server created a new table, but it ignored the instruction to use the InnoDB engine. Please refer to our helpdesk for information on how to resolve this error.');
				$res = new \Symfony\Component\HttpFoundation\Response($html);
				$res->headers->set('Content-Type', 'text/html');
				return $res;
			}
		}

		return $this->render('InstallBundle:Install:install-tables.html.php', array(

		));
	}

	public function doCreateTablesAction($batch = 0)
	{
		if (!$this->ensureNotInstalled()) {
			exit;
		}

		$start = microtime(true);

		if (!defined('DP_BUILD_TIME')) {
			$build_file = DP_ROOT.'/sys/config/build-time.php';
			if (is_file($build_file)) {
				require $build_file;
			} else {
				define('DP_BUILD_TIME', time());
			}
		}

		$schema = null;
		if (file_exists(DP_ROOT.'/src/Application/InstallBundle/Data/schema.php')) {
			$schema = require DP_ROOT.'/src/Application/InstallBundle/Data/schema.php';
		} else {
			$this->getLogger()->log('schema.php does not exist, will auto-generate', 'debug');
		}
		$install_schema = new \Application\InstallBundle\Install\InstallSchema($this->getDb(), $schema, DP_BUILD_TIME);

		$response = new \Symfony\Component\HttpFoundation\Response();
		$response->headers->set('Content-Type', 'text/html');
		$response->sendHeaders();
		flush();
		@ob_flush();

		echo $this->renderView('InstallBundle:Install:install-tables-do.html.php', array());
		echo '<script type="text/javascript">';
		if ($batch == 0) {
			echo 'installStatus.setCount('.$install_schema->countQueries().');';
		}
		echo '</script>';
		flush();

		$limit = 50;
		$skip = $limit * $batch;

		$logger = new \Orb\Log\Logger();
		$install_logger = $this->getLogger();

		$logger->addWriter(new \Orb\Log\Writer\Callback(function($log_item) use ($install_logger) {
			$info = $log_item->toArray();
			if (isset($info['exception'])) {
				$install_logger->log('[InstallTables] Failed: ' . $info['exception']->getCode() . ' ' . $info['exception']->getMessage(), 'err');
				$install_logger->log('[InstallTables] Failed Query: ' . $info['sql'], 'debug');
				$info['error'] = $info['exception']->getMessage();
			}
			unset($info['message_line']);
			unset($info['datetime']);
			unset($info['session_name']);

			echo '<script type="text/javascript">';
			echo 'installStatus.update(' . json_encode($info) . ');';
			echo '</script>';
			flush();
		}));
		$install_schema->setLogger($logger);

		$install_schema->run(false, $limit, $skip);

		echo '<script type="text/javascript">';
		if (($skip + $limit) >= $install_schema->countQueries()) {
			echo 'installStatus.done();';
		} else {
			echo 'installStatus.doneBatch(' . $batch . ');';
		}
		echo '</script>';
		flush();

		echo '</body></html>';

		return new \Symfony\Component\HttpFoundation\Response();
	}

	###############################################################################
	# install-data
	###############################################################################

	public function installDataAction()
	{
		if (!$this->ensureNotInstalled()) {
			return $this->redirect($this->generateUrl('install'));
		}

		$this->getLogger()->log('Install::installData', 'debug');

		return $this->render('InstallBundle:Install:install-data.html.php', array(

		));
	}

	public function installDataSaveAction()
	{
		if (!$this->ensureNotInstalled()) {
			return $this->redirect($this->generateUrl('install'));
		}

		$this->getLogger()->log('Install::installDataSave', 'debug');

		$this->getOrm()->getConnection()->beginTransaction();

		try {
			$agent = new \Application\DeskPRO\Entity\Person();
			$agent->first_name = $this->getIn()->getString('admin.first_name');
			$agent->last_name = $this->getIn()->getString('admin.last_name');
			$agent->setEmail($this->getIn()->getString('admin.email'), true);
			$agent->setPassword($this->getIn()->getString('admin.password'));
			$agent->is_user = true;
			$agent->is_confirmed = true;
			$agent->is_agent_confirmed = true;
			$agent->is_agent = true;
			$agent->can_agent = true;
			$agent->can_admin = true;
			$agent->can_billing = true;
			$agent->can_reports = true;

			$this->getOrm()->persist($agent);
			$this->getOrm()->flush();

			$this->getLogger()->log("New admin: {$agent->id} {$agent->display_name} {$agent->email_address}", 'debug');

			$this->getDb()->insert('permissions', array('person_id' => $agent->id, 'name' => 'admin.use', 'value' => 1));

			// Install data stuff
			$AGENTGROUP_ALL = null; // should be defiend by the time we finish processing data.php
			$USERGROUP_EVERYONE = null; // should be defiend by the time we finish processing data.php
			$AGENT = $agent; // can be used in data.php
			$WEB_INSTALL = true;
			$IMPORT_INSTALL = false;

			$install_data = new \Application\InstallBundle\Install\InstallDataReader(DP_ROOT.'/src/Application/InstallBundle/Data/data.php');
			$em = $this->getOrm();
			foreach ($install_data as $php) {
				eval($php);
			}

			$this->getOrm()->flush();

			// For the all agent group, fetch permissions from the template
			if ($AGENTGROUP_ALL) {
				$scanner = new \Application\InstallBundle\Data\AgentGroupPermScanner();
				foreach ($scanner->getNames() as $p_name) {
					$p = new \Application\DeskPRO\Entity\Permission();
					$p->usergroup = $AGENTGROUP_ALL;
					$p->name = $p_name;
					$p->value = 1;
					$this->getOrm()->persist($p);
				}
				$this->getOrm()->flush();

				$ch = new \Application\DeskPRO\ORM\CollectionHelper($this->getOrm(), $agent, 'usergroups');
				$ch->setCollection(array($AGENTGROUP_ALL));
				$this->getOrm()->persist($agent);
				$this->getOrm()->flush();
			}

			if ($USERGROUP_EVERYONE) {
				$scanner = new \Application\InstallBundle\Data\UserGroupPermScanner();
				foreach ($scanner->getNames() as $p_name) {
					$p = new \Application\DeskPRO\Entity\Permission();
					$p->usergroup = $USERGROUP_EVERYONE;
					$p->name = $p_name;
					$p->value = 1;
					$this->getOrm()->persist($p);
				}
				$this->getOrm()->flush();
			}

			$data_init = new \Application\InstallBundle\Data\DataInitializer($this->container);
			$data_init->run();
			$this->getOrm()->getConnection()->commit();
		} catch (\Exception $e) {
			$this->getLogger()->log("[InstallData] Exception {$e->getCode()} {$e->getMessage()}", 'err');

			$einfo = \DeskPRO\Kernel\KernelErrorHandler::getExceptionInfo($e);
			$this->getLogger()->log("[InstallData] Exception Trace: {$einfo['trace']}", 'debug');

			$this->getOrm()->getConnection()->rollback();
			throw $e;
		}

		$url = $this->generateUrl('install_install_done', array(), true);
		return $this->redirect($url, 302);
	}

	###############################################################################
	# install-done
	###############################################################################

	public function installDoneAction()
	{
		if (!$this->ensureNotInstalled()) {
			return $this->redirect($this->generateUrl('install'));
		}

		$this->getLogger()->log('Install::installDone', 'debug');

		$rewrite_urls = false;
		try {

			$url = App::getRequest()->getUriForPath('/__checkurlrewrite');
			$url_noindex = str_replace('/index.php/', '/', $url);

			$client = new \Zend\Http\Client(null, array('timeout' => 5));
			$client->setMethod(\Zend\Http\Request::METHOD_GET);
			$client->setUri($url_noindex);
			$result = $client->send();
			$this->getLogger()->log('core.rewrite_urls check result: ' . $result->getBody(), 'debug');
			if ($result->isSuccess() && strpos($result->getBody(), 'dp_check_url_ok') !== false) {
				$this->getLogger()->log('Enabling core.rewrite_urls', 'debug');
				$rewrite_urls = true;
			}

		} catch (\Exception $e) {}

		$this->getOrm()->getConnection()->beginTransaction();
		try {
			$db = $this->getOrm()->getConnection();

			$db->replace('settings', array(
				'name' => 'core.done_rewrite_urls_check',
				'value' => time(),
			));
			$db->replace('settings', array(
				'name' => 'core.install_timestamp',
				'value' => time(),
			));
			$db->replace('settings', array(
				'name' => 'core.install_key',
				'value' => Strings::random(20, Strings::CHARS_KEY),
			));
			$db->replace('settings', array(
				'name' => 'core.deskpro_build',
				'value' => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : time(),
			));
			$db->replace('settings', array(
				'name' => 'core.deskpro_version',
				'value' => date('YmdHis'),
			));

			if ($rewrite_urls) {
				$db->replace('settings', array(
					'name' => 'core.rewrite_urls',
					'value' => '1',
				));
			}

			$this->getOrm()->getConnection()->commit();

		} catch (\Exception $e) {
			$this->getLogger()->log("[InstallDone] Exception {$e->getCode()} {$e->getMessage()}", 'err');

			$einfo = \DeskPRO\Kernel\KernelErrorHandler::getExceptionInfo($e);
			$this->getLogger()->log("[InstallDone] Exception Trace: {$einfo['trace']}", 'debug');

			$this->getOrm()->getConnection()->rollback();
			throw $e;
		}

		$agent = $this->getDb()->fetchAssoc("SELECT * FROM people LIMIT 1");

		$base_url = $this->get('request')->getBaseUrl();

		$data = array(
			'log' => @file_get_contents($this->container->getLogDir() . '/install.log'),
			'is_error' => 0,
			'source_type' => 'install.web'
		);
		\Application\DeskPRO\Service\ErrorReporter::sendReport('report-install', $data, 10);

		return $this->redirect($base_url . '/admin/');
	}

	###############################################################################

	/**
	 * @return \Orb\Input\Reader\Reader
	 */
	public function getIn()
	{
		return $this->container->get('deskpro.core.input_reader');
	}

	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getDb()
	{
		return $this->container->get('database_connection');
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getOrm()
	{
		return $this->container->get('doctrine.orm.entity_manager');
	}
}
