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
 * @subpackage DBAL
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

	public $obj_start_time;
	public $log_maxtime   = false;
	public $log_nowhere   = false;
	public $log_countstar = true;
	public $log_all       = false;
	public $log_explain   = false;
	public $log_trace     = false;
	public $min_log_query = 0;

	public $queries = array();

	public function __construct()
	{
		$this->obj_start_time = microtime(true);

		global $DP_CONFIG;
		if (!empty($DP_CONFIG['debug']['querylog']['enabled'])) {
			$this->is_enabled = true;

			if (!empty($DP_CONFIG['debug']['querylog']['log_maxtime'])) {
				$this->log_maxtime = $DP_CONFIG['debug']['querylog']['log_maxtime'];
			}

			if (!empty($DP_CONFIG['debug']['querylog']['log_nowhere'])) {
				$this->log_nowhere = $DP_CONFIG['debug']['querylog']['log_nowhere'];
			}

			if (!empty($DP_CONFIG['debug']['querylog']['log_countstar'])) {
				$this->log_countstar = $DP_CONFIG['debug']['querylog']['log_countstar'];
			}

			if (!empty($DP_CONFIG['debug']['querylog']['log_all'])) {
				$this->log_all = $DP_CONFIG['debug']['querylog']['log_all'];
			}

			if (!empty($DP_CONFIG['debug']['querylog']['log_explain'])) {
				$this->log_explain = $DP_CONFIG['debug']['querylog']['log_explain'];
			}

			if (!empty($DP_CONFIG['debug']['querylog']['log_trace'])) {
				$this->log_trace = $DP_CONFIG['debug']['querylog']['log_trace'];
			}
		}

		if (!empty($DP_CONFIG['debug']['enable_slow_page_log'])) {
			$this->is_enabled = true;
		}

		if (!empty($DP_CONFIG['debug']['enable_slow_page_log_trace'])) {
			$this->log_trace = true;
		}

		if ($this->is_enabled && isset($DP_CONFIG['debug']['enable_slow_page_log_minquerytime']) && $DP_CONFIG['debug']['enable_slow_page_log_minquerytime']) {
			$this->min_log_query = $DP_CONFIG['debug']['enable_slow_page_log_minquerytime'];
		}

		\DpShutdown::add(array($this, 'writeLogQuiet'));
	}

	public function writeLogQuiet()
	{
		try {
			$this->writeLog();
		} catch (\Exception $e) {}
	}

	public function startQuery($sql, array $params = null, array $types = null)
	{
		if ($this->is_logging) return;

		$this->last_query = array(
			'sql'            => $sql,
			'params'         => $params,
			'time_start'     => microtime(true),
			'time_end'       => 0,
			'time_taken'     => 0,
			'trans_level'    => 0
		);
	}

	public function processLast()
	{
		if (!$this->last_query) {
			return;
		}

		if (isset($GLOBALS['DP_NOSQL_LOG'])) return;
		if ($this->is_logging) {
			return;
		}
		if (!$this->is_enabled) {
			return;
		}
		$this->is_logging = true;

		$queryinfo = $this->last_query;

		if (preg_match('#^\s*SELECT#i', $queryinfo['sql'])) {
			$query_typename = 'SELECT';
		} else if (preg_match('#^\s*UPDATE#i', $queryinfo['sql'])) {
			$query_typename = 'UPDATE';
		} else if (preg_match('#^\s*INSERT#i', $queryinfo['sql'])) {
			$query_typename = 'INSERT';
		} else if (preg_match('#^\s*DELETE#i', $queryinfo['sql'])) {
			$query_typename = 'DELETE';
		} else {
			$query_typename = 'OTHER';
		}
		$queryinfo['query_typename'] = $query_typename;
		$queryinfo['params_string'] = \DeskPRO\Kernel\KernelErrorHandler::varToString($queryinfo['params']);
		$queryinfo['time_end']   = microtime(true);
		$queryinfo['time_taken'] = $queryinfo['time_end'] - $queryinfo['time_start'];
		$queryinfo['time_taken_str'] = sprintf('%.2f', $queryinfo['time_taken']);
		$queryinfo['trans_level'] = App::getDb()->getTransactionNestingLevel();

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
			try {
				if ($this->log_explain && $queryinfo['query_typename'] == 'SELECT') {
					try {
						$explain = App::getDb()->fetchAll("EXPLAIN {$queryinfo['sql']}", $queryinfo['params']);
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
						'trace'   => $queryinfo['trace'],
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
		$this->last_query['time_end']   = microtime(true);
		$this->last_query['time_taken'] = $this->last_query['time_end'] - $this->last_query['time_start'];
		$this->last_query['memory']     = memory_get_usage();

		if ($this->is_enabled && (!$this->min_log_query || $this->last_query['time_taken'] >= $this->min_log_query)) {
			if ($this->log_trace) {
				try { throw new \Exception(); } catch (\Exception $e) { $trace = \DeskPRO\Kernel\KernelErrorHandler::formatBacktrace($e->getTrace()); }
				$this->last_query['trace'] = $trace;
			}
			$this->queries[] = $this->last_query;
		}

		$this->query_count++;
		if (!isset($GLOBALS['DP_QUERY_COUNT'])) {
			$GLOBALS['DP_QUERY_COUNT'] = 0;
		}
		$GLOBALS['DP_QUERY_COUNT']++;
		$this->total_time += $this->last_query['time_taken'];
	}

	public function writeLog()
	{
		global $DP_CONFIG;
		if (!isset($DP_CONFIG['debug']['enable_slow_page_log']) OR !$DP_CONFIG['debug']['enable_slow_page_log']) {
			return;
		}

		if (isset($DP_CONFIG['debug']['slow_page_log_ignorenourl']) && $DP_CONFIG['debug']['slow_page_log_ignorenourl'] && (!defined('DP_REQUEST_URL') || !DP_REQUEST_URL)) {
			return;
		}

		if (isset($DP_CONFIG['debug']['enable_slow_page_log_simplelog']) && $DP_CONFIG['debug']['enable_slow_page_log_simplelog']) {
			$this->writeLogSimple();
			return;
		}

		if (defined('DP_START_TIME')) {
			$start_time = DP_START_TIME;
		} else {
			$start_time = $this->obj_start_time;
		}

		$total_time = microtime(true) - $start_time;
		$db_time    = $this->total_time;
		$php_time   = $total_time - $db_time;

		$do_log = false;
		if (is_numeric($DP_CONFIG['debug']['enable_slow_page_log'])) {
			if ($total_time > $DP_CONFIG['debug']['enable_slow_page_log']) {
				$do_log = true;
			}
		} else {
			if ($DP_CONFIG['debug']['enable_slow_page_log'] && $this->queries) {
				$do_log = true;
			}
		}

		if ($do_log) {
			$write = array("--- Page Log Begin ---\n");
			if (defined('DP_REQUEST_URL')) {
				$write[] = "=> URL: " . DP_REQUEST_URL . "\n";
			}

			$write[] = sprintf("=> Time: %.4f    PHP_Time: %.4f    DB_Time: %.4f    Query_Count: %d    Peak_Memory: %d\n", $total_time, $php_time, $db_time, $this->query_count, memory_get_peak_usage());

			$hashes_to_name = array();
			$count = 0;
			$name_counts = array();
			$name_counts_time = array();

			foreach ($this->queries as $q) {

				$sql = trim($q['sql']);
				$hash = md5($q['sql']);

				if (!isset($hashes_to_name[$hash])) {
					$hashes_to_name[$hash] = sprintf('query_%04d', $count);
					$count++;
				}

				$name = $hashes_to_name[$hash];

				if (!isset($name_counts[$name])) {
					$name_counts[$name] = 0;
					$name_counts_time[$name] = 0.0;
				}

				$name_counts[$name]++;
				$name_counts_time[$name] += $q['time_taken'];

				$sql = str_replace(array("\r\n", "\n", "\t"), ' ', $sql);
				$sql = preg_replace('# {2,}#', ' ', $sql);
				$sql = substr($sql, 0, 5000);

				$params = array();
				if ($q['params']) {
					foreach ($q['params'] as $v) {
						if (is_numeric($v) || ctype_digit($v)) {
							$params[] = $v;
						} elseif (is_string($v)) {
							$v = str_replace(array("\r\n", "\n", "\t"), ' ', $v);
							$v = preg_replace('# {2,}#', ' ', $v);

							if (strlen($v) > 100) {
								$v = substr($v, 0, 100);
							}

							$params[] = 'string:' . $v;
						} elseif ($v === null) {
							$params[] = 'NULL';
						} elseif (is_array($v)) {
							$params[] = substr(\DeskPRO\Kernel\KernelErrorHandler::varToString($v), 0, 200);
						} elseif (is_object($v)) {
							$params[] = get_class($v);
						} else {
							$params[] = gettype($v);
						}
					}
				}

				$m = null;
				if (preg_match('# FROM ([a-zA-Z_]+)#', $sql, $m)) {
					$table = $m[1];
				} elseif (preg_match('#INSERT INTO ([a-zA-Z_]+)#', $sql, $m)) {
					$table = $m[1];
				} elseif (preg_match('#UPDATE ([a-zA-Z_]+)#', $sql, $m)) {
					$table = $m[1];
				} elseif (preg_match('#DELETE FROM ([a-zA-Z_]+)#', $sql, $m)) {
					$table = $m[1];
				} else {
					$table = '';
				}

				if ($table) {
					$table = ' ' . $table;
				}

				if (!isset($q['trans_level'])) {
					$q['trans_level'] = 0;
				}

				$memory = $q['memory'] / 1024;

				$write[] = sprintf("%s> Query %.4f %4dK %s$table: %s \t\t Query_Params: %s\n", str_repeat('=', $q['trans_level']+1), $q['time_taken'], $memory, $name, $sql, implode(', ', $params));
				if (isset($q['trace'])) {
					$write[] = \Orb\Util\Strings::modifyLines($q['trace'], "   ", '', true);
					$write[] = "\n";
				}
			}

			foreach ($name_counts as $name => $count) {
				if ($count > 1) {
					$write[] = sprintf("\n=> Repeated_Query %s: %s times    Total_Time: %.4f", $name, $count, $name_counts_time[$name]);
				}
			}

			$write = implode('', $write);
			$write = trim($write);

			$prefix = '[' . date('Y-m-d H:i:s') . '] ';
			$write = \Orb\Util\Strings::modifyLines($write, $prefix);
			$write = trim($write);
			$write .= "\n";

			$path = dp_get_log_dir().'/slow-page-log.log';
			if (isset($DP_CONFIG['debug']['enable_slow_page_log_filepath']) && $DP_CONFIG['debug']['enable_slow_page_log_filepath']) {
				$path = $DP_CONFIG['debug']['enable_slow_page_log_filepath'];
			}

			file_put_contents($path, $write, \FILE_APPEND | \LOCK_EX);

			// If we just created the file this will make it writable
			// in case the same file is being writ to by the CLI and web server both
			@chmod($path, 0777);
		}
	}

	public function writeLogSimple()
	{
		global $DP_CONFIG;

		if (defined('DP_START_TIME')) {
			$start_time = DP_START_TIME;
		} else {
			$start_time = $this->obj_start_time;
		}

		$total_time = microtime(true) - $start_time;
		$db_time    = $this->total_time;
		$php_time   = $total_time - $db_time;

		if ($total_time < $DP_CONFIG['debug']['enable_slow_page_log']) {
			return;
		}

		$url = '';
		if (defined('DP_REQUEST_URL')) {
			$url = DP_REQUEST_URL;
		}
		if (!$url) {
			return;
		}

		// Trim off _rt
		$url = preg_replace('#(\??)&?_rt=[a-zA-Z0-9]+\-[a-zA-Z0-9]+\-[a-f0-9]+#', '$1', $url);

		// Trim of _=1434343 cache buster
		$url = preg_replace('#(\??)&?_=([0-9]+)#', '$1', $url);

		// trim off single trailing ?
		$url = rtrim($url, '?&');

		$write = sprintf("[%s] Time: %.4f    PHP_Time: %.4f    DB_Time: %.4f    Query_Count: %d    Peak_Memory: %d    URL: %s\n", date('Y-m-d H:i:s'), $total_time, $php_time, $db_time, $this->query_count, memory_get_peak_usage(), $url);

		$path = dp_get_log_dir().'/slow-page-simplelog.log';
		if (isset($DP_CONFIG['debug']['enable_slow_page_log_filepath']) && $DP_CONFIG['debug']['enable_slow_page_log_filepath']) {
			$path = $DP_CONFIG['debug']['enable_slow_page_log_filepath'];
		}

		file_put_contents($path, $write, \FILE_APPEND | \LOCK_EX);
	}
}
