<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage InstallBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\InstallBundle\Install;

use Application\DeskPRO\DBAL\Connection;
use Orb\Log\Logger;

class InstallSchema
{
	/**
	 * Plain database connection for raw queries
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @param array $schema
	 */
	protected $schema;

	/**
	 * @var \Application\DeskPRO\Log\Logger
	 */
	protected $logger = null;


	/**
	 * @param \Application\DeskPRO\DBAL\Connection $db
	 * @param array $schema
	 */
	public function __construct($db, array $schema)
	{
		$this->db = $db;
		$this->schema = $schema;
	}


	/**
	 * @param \Application\DeskPRO\Log\Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}

	protected function getLogger()
	{
		if ($this->logger === null) {
			$this->logger = new \Orb\Log\Logger();
		}

		return $this->logger;
	}

	public function countQueries()
	{
		return count($this->schema['create']) + count($this->schema['alter']);
	}

	/**
	 * Run through all the queries
	 *
	 * @param bool $halt_on_error True to stop and throw an exception when an error is encountered.
	 * @return bool True on success, false on error
	 */
	public function run($halt_on_error = true, $limit = 1000000, $skip = 0)
	{
		$has_error = false;

		$s_time = microtime(true);
		$this->getLogger()->log("InstallSchema::run started " . sprintf("%.f", $s_time), Logger::DEBUG);

		if (!$this->schema['create']) $this->schema['create'] = array();
		if (!$this->schema['alter']) $this->schema['alter'] = array();

		if ($limit) {
			foreach ($this->schema['create'] as $k => $sql) {

				if ($skip) {
					$skip--;
					continue;
				}

				$this->getLogger()->log("[QUERY:TABLE:$k] $sql", Logger::DEBUG, array('sql' => $sql));

				try {
					$this->db->exec($sql);
				} catch (\Exception $e) {
					$has_error = true;
					if (strlen($sql) > 30) {
						$sub = substr($sql, 0, 30) . '...';
					} else {
						$sub = $sql;
					}
					$this->getLogger()->log("[QUERY:TABLE:$k] FAILED: {$e->getMessage()} in query: $sub", Logger::CRIT, array('type' => 'alter', 'sql' => $sql, 'exception' => $e));
					if ($halt_on_error) {
						throw $e;
					}
				}

				$limit--;
				if (!$limit) {
					break;
				}
			}
		}

		if ($limit) {
			foreach ($this->schema['alter'] as $k => $sql) {
				if ($skip) {
					$skip--;
					continue;
				}

				$this->getLogger()->log("[QUERY:ALTER:$k] $sql", Logger::DEBUG, array('sql' => $sql));

				try {
					$this->db->exec($sql);
				} catch (\Exception $e) {
					$has_error = true;
					if (strlen($sql) > 30) {
						$sub = substr($sql, 0, 30) . '...';
					} else {
						$sub = $sql;
					}
					$this->getLogger()->log("[QUERY:ALTER:$k] FAILED: {$e->getMessage()} in query: $sub", Logger::CRIT, array('type' => 'alter', 'sql' => $sql, 'exception' => $e));
					if ($halt_on_error) {
						throw $e;
					}
				}

				$limit--;
				if (!$limit) {
					break;
				}
			}
		}

		$e_time = microtime(true);
		$this->getLogger()->log("InstallSchema::run finished " . sprintf("%.f (took %.fs)", $e_time, $e_time - $s_time), Logger::DEBUG);

		if ($has_error) {
			return false;
		}

		return true;
	}
}
