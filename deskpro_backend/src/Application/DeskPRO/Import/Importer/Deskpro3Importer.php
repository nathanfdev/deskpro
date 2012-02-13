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

	public function postRunStep($step)
	{
		gc_collect_cycles();
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
