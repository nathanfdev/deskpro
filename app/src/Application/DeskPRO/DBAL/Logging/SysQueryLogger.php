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

use Application\DeskPRO\App;

class SysQueryLogger extends \Symfony\Bridge\Doctrine\Logger\DbalLogger
{
	public $query_count = 0;
	public $total_time = 0.0;
	public $is_enabled = false;
	public $is_logging = false;
	public $last_query = null;

	public $log_maxtime   = false;
	public $log_nowhere   = false;
	public $log_countstar = true;
	public $log_all       = false;
	public $log_explain   = false;
	public $log_trace     = false;

	public function __construct()
	{
		global $DP_CONFIG;
		if (isset($DP_CONFIG['debug']['querylog']) && $DP_CONFIG['debug']['querylog']['enabled']) {
			$this->is_enabled = true;

			if (isset($DP_CONFIG['debug']['querylog']['log_maxtime'])) {
				$this->log_maxtime = $DP_CONFIG['debug']['querylog']['log_maxtime'];
			}

			if (isset($DP_CONFIG['debug']['querylog']['log_nowhere'])) {
				$this->log_nowhere = $DP_CONFIG['debug']['querylog']['log_nowhere'];
			}

			if (isset($DP_CONFIG['debug']['querylog']['log_countstar'])) {
				$this->log_countstar = $DP_CONFIG['debug']['querylog']['log_countstar'];
			}

			if (isset($DP_CONFIG['debug']['querylog']['log_all'])) {
				$this->log_all = $DP_CONFIG['debug']['querylog']['log_all'];
			}

			if (isset($DP_CONFIG['debug']['querylog']['log_explain'])) {
				$this->log_explain = $DP_CONFIG['debug']['querylog']['log_explain'];
			}

			if (isset($DP_CONFIG['debug']['querylog']['log_trace'])) {
				$this->log_explain = $DP_CONFIG['debug']['querylog']['log_trace'];
			}
		}

		register_shutdown_function(array($this, 'processLast'));
	}

	public function startQuery($sql, array $params = null, array $types = null)
	{
		if (isset($GLOBALS['DP_NOSQL_LOG'])) return;
		if ($this->is_logging) return;
		if (!$this->is_enabled) return;

		$this->processLast();

		$sql = trim($sql);
		if (preg_match('#^SELECT#i', $sql)) {
			$query_typename = 'SELECT';
		} else if (preg_match('#^UPDATE#i', $sql)) {
			$query_typename = 'UPDATE';
		} else if (preg_match('#^INSERT#i', $sql)) {
			$query_typename = 'INSERT';
		} else if (preg_match('#^DELETE#i', $sql)) {
			$query_typename = 'DELETE';
		} else {
			$query_typename = 'OTHER';
		}

		$params_string = \DeskPRO\Kernel\KernelErrorHandler::varToString($params);

		$this->last_query = array(
			'sql'            => $sql,
			'params'         => $params,
			'params_string'  => $params_string,
			'types'          => $types,
			'query_typename' => $query_typename,
			'time_start'     => microtime(true),
			'time_end'       => 0,
			'time_taken'     => 0
		);
	}

	public function processLast()
	{
		if (isset($GLOBALS['DP_NOSQL_LOG'])) return;
		if ($this->is_logging) {
			return;
		}

		if (!$this->is_enabled OR !$this->last_query) {
			return;
		}
		$this->is_logging = true;

		$queryinfo = $this->last_query;
		$queryinfo['time_end']   = microtime(true);
		$queryinfo['time_taken'] = $queryinfo['time_end'] - $queryinfo['time_start'];
		$queryinfo['time_taken_str'] = sprintf('%.2f', $queryinfo['time_taken']);

		$this->query_count++;
		$this->total_time += $queryinfo['time_taken'];

		if (preg_match('#\s+(FROM|INSERT INTO|UPDATE|DELETE FROM)\s+(.*?)\s+#', $queryinfo['sql'], $m)) {
			$table = $m[2];
		} else {
			$table = '(unknown table)';
		}

		$do_log = false;
		if ($this->log_all) {
			$do_log = true;
		}
		if (!$do_log && $this->log_countstar) {
			if (strpos($queryinfo['time_sql'], 'COUNT(*)') !== false && strpos($queryinfo['time_sql'], 'WHERE') === false) {
				$do_log = true;
			}
		}
		if (!$do_log && $this->log_nowhere) {
			if ($this->log_nowhere === true || $this->log_nowhere <= $queryinfo['time_taken']) {
				$do_log = true;
			}
		}
		if (!$do_log && $this->log_maxtime && $this->log_maxtime <= $queryinfo['time_taken']) {
			$do_log = true;
		}

		if ($do_log) {
			$explain = '';
			$trace = '';
			try {
				if ($this->log_trace) {
					try { throw new \Exception(); } catch (\Exception $e) { $trace = \DeskPRO\Kernel\KernelErrorHandler::formatBacktrace($e->getTrace()); }
				}

				if ($this->log_explain && $queryinfo['query_typename'] == 'SELECT') {
					try {
						$explain = App::getDb()->fetchAll("EXPLAIN $sql", $queryinfo['params']);
					} catch (\Exception $e) {}
				}

				$db = App::getDb();
				$db->insert('log_items', array(
					'log_name' => 'query_log',
					'priority' => 7,
					'priority_name' => 'DEBUG',
					'message' => "[{$queryinfo['time_taken_str']} {$table}] {$queryinfo['sql']}",
					'data' => serialize(array(
						'time'    => sprintf('%.8f', $queryinfo['time_taken']),
						'sql'     => $queryinfo['sql'],
						'params'  => $queryinfo['params_string'],
						'trace'   => $trace,
						'explain' => $explain,
					))
				));
			} catch (\Exception $e) {}
		}


		$this->last_query = null;
		$this->is_logging = false;
	}

	public function stopQuery()
	{
		// Dont process anything here
		// It'll interfere with mysql's last insert ID if we insert log items now (it'll return the log items id!)
		// Instead, only process when processing a new query, and also we registered a shutdown function to
		// process the last query on the page.
	}
}
