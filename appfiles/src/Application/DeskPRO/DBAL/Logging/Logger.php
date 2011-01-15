<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage DBAL
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DBAL\Logging;

use \Orb\Log\Logger;

/**
 * Log various query information
 */
class Logger implements \Doctrine\DBAL\Logging\SQLLogger
{
	const TYPE_SELECT = 1;
	const TYPE_UPDATE = 2;
	const TYPE_INSERT = 4;
	const TYPE_DELETE = 8;
	const TYPE_OTHER  = 16;
	const TYPE_ALL    = 31;

	/**
	 * @var Orb\Log\Logger
	 */
	protected $_logger = null;

	protected $_is_enabled = true;
	protected $_slowlog_rules = array();

	protected $_queries = array();
	protected $_last_query = -1;

	protected $_query_counter = 0;
	protected $_query_total_time = 0.0;
	
	public function startQuery($sql, array $params = null, array $types = null)
	{
		if (!$this->_is_slowlog_enabled) return;

		$sql = trim($sql);
		if (preg_match('#^SELECT#i', $sql)) {
			$query_type = self::TYPE_SELECT;
		} else if (preg_match('#^UPDATE#i', $sql)) {
			$query_type = self::TYPE_UPDATE;
		} else if (preg_match('#^INSERT#i', $sql)) {
			$query_type = self::TYPE_INSERT;
		} else if (preg_match('#^DELETE#i', $sql)) {
			$query_type = self::TYPE_DELETE;
		} else {
			$query_type = self::TYPE_OTHER;
		}

		$this->_last_query++;

		$this->_queries[$this->_last_query] = array(
			'sql'        => $sql,
			'params'     => $params,
			'types'      => $types,
			'query_type' => $query_type,
			'time_start' => microtime(true),
			'time_end'   => 0,
			'time_taken' => 0
		);
	}

	public function stopQuery()
	{
		if (!$this->_is_slowlog_enabled OR $this->_last_query == -1) return;

		$queryinfo = &$this->_queries[$this->_last_query];
		$queryinfo['time_end']   = microtime(true);
		$queryinfo['time_taken'] = $queryinfo['time_end'] - $queryinfo['time_start'];

		$this->_query_counter++;
		$this->_query_total_time += $queryinfo['time_taken'];

		if ($this->_logger) {
			foreach ($this->_slowlog_rules as $rule) {
				if (($queryinfo['query_type'] & $rule[0]) AND $queryinfo['time_taken'] >= $rule[1]) {
					$this->_logger->log('Slow query: ' . $queryinfo['sql'], Logger::NOTICE, array('queryinfo' => $queryinfo));
					break;
				}
			}
		}
	}


	
	/**
	 * Get all query info we've logged
	 *
	 * @return array
	 */
	public function getQueries()
	{
		return $this->_queries;
	}

	
	
	/**
	 * Is this logger currently enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->_is_enabled;
	}
	
	
	
	/**
	 * Enable this logger
	 */
	public function enable()
	{
		$this->_is_enabled = true;
	}


	
	/**
	 * Disable this logger
	 */
	public function disable()
	{
		$this->_is_enabled = false;
	}



	/**
	 * Add a slow logging rule
	 * 
	 * @param <type> $query_type
	 * @param <type> $max_time
	 */
	public function addSlowLogRule($query_type, $max_time)
	{
		$this->_slowlog_rules[] = array($query_type, $max_time);
	}



	/**
	 * Get the slow log rules currently set.
	 *
	 * @return array
	 */
	public function getSlowLogRules()
	{
		return $this->_slowlog_rules;
	}



	/**
	 * Set a specific array of slowlog rules. Rules must be an array of array(type, maxtime).
	 * @param array $rules
	 */
	public function setSlowLogRules(array $slowlog_rules)
	{
		$this->_slowlog_rules = $slowlog_rules;
	}



	/**
	 * Set the logger
	 *
	 * @param Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->_logger = $logger;
	}



	/**
	 * Get the assigned logger.
	 *
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->_logger;
	}
}