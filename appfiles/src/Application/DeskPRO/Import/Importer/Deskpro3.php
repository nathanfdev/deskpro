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
	protected $db;
	protected $old_db;

	protected $steps = array(
		'Settings',
		'EmailAccounts',
		'Banning',
		'QuickReplies',
		'TechPms',
		'SelfHelp',
		'News',
		'Tasks',
		'Chat',
		'Users',
		'Tickets',
		'Attachments',
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
				'driver' => 'pdo_mysql',
				'host' => $this->config->db_host,
				'user' => $this->config->db_user,
				'password' => $this->config->db_password,
				'dbname' => $this->config->db_name
			));
		} catch (\Exception $e) {
			$errors[] = "Failed connecting to DeskPRO v3 database: {$e->getMessage()}";
		}

		return $errors;
	}


	public function setupImport()
	{
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
		$class = $this->steps[$step];
		$step = new $class($this);

		return $step;
	}
}
