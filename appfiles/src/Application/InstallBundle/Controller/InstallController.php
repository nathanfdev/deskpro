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

namespace Application\InstallBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;

/**
 * Installation
 */
class InstallController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
	###############################################################################
	# index
	###############################################################################

	public function indexAction()
	{
		$server_check = new \Application\InstallBundle\Install\ServerChecks();
		$server_check->checkServer();

		$is_fatal = $server_check->hasFatalErrors();

		$has_config = false;
		$has_db_checks = false;

		if (!$is_fatal) {
			$has_db_checks = true;
			if (file_exists(DP_CONFIG_FILE)) {
				$has_config = true;
				$server_check->checkDatabase(App::getConfig('db'));
			}
		}

		$is_fatal = $server_check->hasFatalErrors();

		return $this->render('InstallBundle:Install:index.html.php', array(
			'errors' => $server_check->getErrors(),
			'has_config' => $has_config,
			'is_fatal' => $is_fatal,
			'has_db_checks' => $has_db_checks
		));
	}

	###############################################################################
	# verify-files
	###############################################################################

	public function verifyFilesAction()
	{
		$verify = new \Application\DeskPRO\Distribution\VerifyChecksums();
		$count  = $verify->countChunks();

		return $this->render('InstallBundle:Install:verify-files.html.php', array(
			'count' => $count,
		));
	}

	public function doVerifyFilesAction($batch = 0)
	{
		$verify = new \Application\DeskPRO\Distribution\VerifyChecksums();
		$results = $verify->compareChunk($batch);

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
				$html = deskpro_install_basic_error('There was a problem trying to create the first database table `install_data`: ' . $e->getCode() . ' ' . $e->getMessage());
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
		if (!defined('DP_BUILD_TIME')) {
			$build_file = DP_ROOT.'/sys/config/build-time.php';
			if (is_file($build_file)) {
				require $build_file;
			} else {
				define('DP_BUILD_TIME', time());
			}
		}

		$schema = require DP_ROOT.'/src/Application/InstallBundle/Data/schema.php';
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

		$limit = 20;
		$skip = $limit * $batch;

		$logger = new \Orb\Log\Logger();
		$logger->addWriter(new \Orb\Log\Writer\Callback(function($log_item) {
			$info = $log_item->toArray();
			if (isset($info['exception'])) {
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
		return $this->render('InstallBundle:Install:install-data.html.php', array(

		));
	}

	public function installDataSaveAction()
	{
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

			$this->getDb()->insert('permissions', array('person_id' => $agent->id, 'name' => 'admin.use', 'value' => 1));

			// Install data stuff
			$install_data = new \Application\InstallBundle\Install\InstallDataReader(DP_ROOT.'/src/Application/InstallBundle/Data/data.php');
			$em = $this->getOrm();
			foreach ($install_data as $php) {
				eval($php);
			}

			$this->getOrm()->flush();
			$this->getOrm()->getConnection()->commit();
		} catch (\Exception $e) {
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
		$db = $this->getOrm()->getConnection();
		$db->insert('settings', array(
			'name' => 'core.install_timestamp',
			'groupname' => 'core',
			'value' => time(),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));
		$db->insert('settings', array(
			'name' => 'core.install_key',
			'groupname' => 'core',
			'value' => Strings::random(20, Strings::CHARS_KEY),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));

		$agent = $this->getDb()->fetchAssoc("SELECT * FROM people LIMIT 1");

		$base_url = $this->get('request')->getBaseUrl();

		return $this->render('InstallBundle:Install:install-done.html.php', array(
			'agent' => $agent,
			'base_url' => $base_url,
		));
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
