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

namespace Application\DeskPRO\Import\Importer;

use Orb\Log\Logger;
use Application\DeskPRO\DBAL\Logging\QueryLogger;

class Deskpro3Importer extends AbstractImporter
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $old_db;

	/**
	 * @var int
	 */
	protected $time_begin = 0;

	protected $steps = array(
		'Settings',
		'Banning',
		'CustomFields',
		'TicketCategories',
		'TicketPriorities',
		'TicketWorkflows',
		'Usergroups',
		'Companies',
		'PopAccounts',
		'Techs',
		'Users',
		'TechPms',
		'KbBlobs',
		'FileBlobs',
		'TicketBlobs',
		'KbCats',
		'Kb',
		'Downloads',
		'FeedbackCats',
		'Feedback',
		'UserNews',
		'Tasks',
		'Tickets',
		'Misc',
		'BackupTroubles',
	);

	public function validateOptions()
	{
		$errors = array();

		foreach (array('db_host', 'db_user', 'db_password', 'db_name') as $k) {
			if (!$this->config->has($k)) {
				$errors[] = "Missing configuration value: import.$k";
			}
		}

		try {
			$this->old_db = $this->container->get('doctrine.dbal.connection_factory')->createConnection(array(
				'driver'   => 'pdo_mysql',
				'host'     => $this->config->db_host,
				'user'     => $this->config->db_user,
				'password' => $this->config->db_password,
				'dbname'   => $this->config->db_name
			));
			$this->logMessage("-- OK");
		} catch (\Exception $e) {
			$this->logMessage("-- FAILED");
			$errors[] = "Failed connecting to DeskPRO v3 database: {$e->getMessage()}";
		}

		return $errors;
	}

	public function setupImport()
	{
		gc_enable();
		$this->db = $this->container->getDb();
	}


	public function cleanupImport()
	{
	}


	public function countSteps()
	{
		return count($this->steps);
	}


	public function getStep($step)
	{
		$class = 'Application\\DeskPRO\\Import\\Importer\\Step\\Deskpro3\\' . $this->steps[$step-1] . 'Step';
		$step = new $class($this);

		return $step;
	}

	/**
	 * Called before a step is run
	 *
	 * @param $step
	 */
	public function preRunStep($step)
	{
		$formatter = new \Orb\Log\Filter\CallbackFormatter(function ($log_item) {
			/** @var $log_item \Orb\Log\LogItem */
			$log_item = $log_item;

			$extra = $log_item->getExtra();
			$queryinfo = $extra['queryinfo'];

			$mem = @memory_get_usage();
			$mem = \Orb\Util\Numbers::filesizeDisplay($mem);

			$log_item[\Orb\Log\LogItem::MESSAGE_LINE] = sprintf(
				"[%s time:%0.2fs mem:%s]\n\t%s\n\n\t%s\n\n\n",
				$queryinfo['query_typename'],
				$queryinfo['time_taken'],
				$mem,
				\DeskPRO\Kernel\KernelErrorHandler::varToString($queryinfo['params']),
				$queryinfo['sql']
			);

			return $log_item;
		});

		// For current database connection
		$qlog = new QueryLogger();

		if ($this->config->get('enable_query_log')) {
			$logger = new \Orb\Log\Logger();
			$logger->addFilter($formatter);
			$logger->addWriter(new \Orb\Log\Writer\Stream($this->config->get('log_dir') . '/importer-db-sql.log', null, false));

			$qlog->setLogger($logger);
			$qlog->addSlowLogRule(QueryLogger::TYPE_ALL, 0);
		}

		$this->qlog_db = $qlog;
		$this->db->getConfiguration()->setSQLLogger($qlog);

		// For olddb too
		if ($this->config->get('enable_query_log')) {
			$logger = new \Orb\Log\Logger();
			$logger->addFilter($formatter);
			$logger->addWriter(new \Orb\Log\Writer\Stream($this->config->get('log_dir') . '/importer-olddb-sql.log', null, false));

			$qlog->setLogger($logger);
			$qlog->addSlowLogRule(QueryLogger::TYPE_ALL, 0);
		}

		$this->qlog_olddb = new QueryLogger();
		$this->getOldDb()->getConfiguration()->setSQLLogger($qlog);

		$this->time_begin = microtime(true);
	}


	/**
	 * Called after a step is run
	 *
	 * @param $step
	 */
	public function postRunStep($step)
	{
		gc_collect_cycles();

		$time_end   = microtime(true);
		$time_total = $time_end - $this->time_begin;

		$time_db    = $this->qlog_db->total_time;
		$time_olddb = $this->qlog_olddb->total_time;

		$time_db_total  = $time_db + $time_olddb;
		$time_php_total = $time_total - $time_db_total;

		$total_queries = $this->qlog_db->query_count + $this->qlog_olddb->query_count;

		$mem = @memory_get_peak_usage();
		if (!$mem) {
			$mem = 0;
		}
		$mem = \Orb\Util\Numbers::filesizeDisplay($mem);

		$this->logMessage(sprintf("Time: %0.2f   PHP: %0.2f   DB: %0.2f (db %0.2f, olddb %0.2f)  Queries: %d,  Peak Mem: %s", $time_total, $time_php_total, $time_db_total, $time_db, $time_olddb, $total_queries, $mem));
	}


	public function getStepTitle($step)
	{
		$class = 'Application\\DeskPRO\\Import\\Importer\\Step\\Deskpro3\\' . $this->steps[$step-1] . 'Step';
		return $class::getTitle();
	}

	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getOldDb()
	{
		return $this->old_db;
	}
}
