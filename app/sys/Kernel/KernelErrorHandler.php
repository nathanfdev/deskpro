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
 */

namespace DeskPRO\Kernel;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

use Application\DeskPRO\App;

class KernelErrorHandler
{
	public static $is_logging = false;
	public static $is_handling_exception = false;
	public static $wrote_log_file = false;
	public static $wrote_php_log = false;
	protected static $process_log = array();


	/**
	 * Add a log line that'll be saved with an error. This is used with things like the gateway, where
	 * if there's an error we'll want to know everything that happened up to the error point.
	 *
	 * @param $line
	 */
	public static function addProcessLog($line)
	{
		self::$process_log[] = $line;
	}


	/**
	 * Clears the process log. For example, with the gateway, if a new email is started then the last log
	 * might be cleared.
	 */
	public static function clearProcessLog()
	{
		self::$process_log = array();
	}


	/**
	 * Handle an error. Typically used as the error handler with set_error_handler()
	 *
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param string $errline
	 * @return void
	 */
	public static function handleError($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			return;
		}

		$errinfo = self::getErrorInfo($errno, $errstr, $errfile, $errline);

		// PDO::__construct on Windows sometimes doesnt listen to the PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		// option. So it'll generate warnings instead of exceptions.
		// This tries to catch those cases, and turn them into exceptions.
		if (strpos($errinfo['errstr'], 'PDO::__construct') !== false) {
			$code = $errinfo['errno'];
			if (preg_match('#PDO::__construct\\(\\): \\[(.*?)\\]#', $errinfo['errstr'], $m)) {
				$code = $m[1];
			}
			$pdo_e = new \PDOException($errinfo['errstr'], $code);
			throw $pdo_e;
		}


		self::logErrorInfo($errinfo);

		if ($errinfo['display']) {
			echo $errinfo['summary'];

			if (isset($GLOBALS['DP_IS_IN_CLI'])) {
				if (self::$wrote_log_file) echo "\n(Refer to " . self::$wrote_log_file . " for details)\n";
				if (self::$wrote_php_log) echo "\n(Refer to the PHP erorr log for details)\n";
			}
		}

		try {
			if (!empty($GLOBALS['DP_ERR_LOGGER'])) {
				$logger = $GLOBALS['DP_ERR_LOGGER'];
				$logger->log($errinfo['summary'] . "\n" . $errinfo['trace'], 'ERR', array('errinfo' => $errinfo));
			}
		} catch (\Exception $e) {}

		if ($errinfo['die']) {
			self::tryCleanup();
			exit(1);
		}
	}


	/**
	 * Handle logging of an exception.
	 *
	 * @param \Exception $exception
	 * @return void
	 */
	public static function handleException(\Exception $exception, $exit = true)
	{
		if (self::$is_handling_exception) {
			return;
		}

		// Dont log 404's
		if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
			return;
		}
		if ($exception instanceof \Application\DeskPRO\HttpKernel\Exception\NoPermissionException) {
			return;
		}

		self::$is_handling_exception = true;

		$errinfo = self::getExceptionInfo($exception);
		self::logErrorInfo($errinfo);

		if ($errinfo['display']) {
			echo $errinfo['summary'];

			if (isset($GLOBALS['DP_IS_IN_CLI'])) {
				if (self::$wrote_log_file) echo "\n(Refer to " . self::$wrote_log_file . " for details)\n";
				else echo "\n(Refer to the PHP erorr log for details)\n";
			}
		}

		try {
			if (!empty($GLOBALS['DP_ERR_LOGGER'])) {
				$logger = $GLOBALS['DP_ERR_LOGGER'];
				$logger->log($errinfo['summary'] . "\n" . $errinfo['trace'], 'ERR', array('errinfo' => $errinfo));
			}
		} catch (\Exception $e) {}

		if ($errinfo['die'] && $exit) {

			self::tryCleanup();

			$code = (int)$errinfo['exception']->getCode();
			if ($code > 255) $code = 255;
			if ($code == 0) $code = 1;
			exit($code);
		}

		self::$is_handling_exception = false;
	}


	/**
	 * Tries to clean up before dieing after a fatal error.
	 */
	public static function tryCleanup()
	{
		if (class_exists('Application\DeskPRO\App')) {
			try {
				$db = App::getDb();
				if ($db->isTransactionActive()) {
					$db->rollback();
				}
			} catch (\Exception $e) {}
		}
	}


	/**
	 * @static
	 *
	 */
	public static function genSessionName()
	{
		static $counter = 0;

		list($time, $ms) = explode(' ', microtime());

		return self::_encodeNum($time) . self::_encodeNum($ms) . self::_encodeNum(++$counter);
	}

	protected static function _encodeNum($num)
	{
		$alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		$arr = array();
		$base = strlen($alphabet);

		while ($num) {
			$rem = $num % $base;
			$num = (int)($num / $base);
			$arr[] = $alphabet[$rem];
		}

		$arr = array_reverse($arr);
		return implode('', $arr);
	}


	/**
	 * Takes care of logging an error. $errinfo is an info array from getExceptionInfo or getErrorInfo.
	 *
	 * @param array $errinfo
	 * @return void
	 */
	public static function logErrorInfo(array $errinfo)
	{
		if (self::$is_logging) return;
		self::$is_logging = true;

		if (!class_exists('Application\DeskPRO\App')) {
			return;
		}

		if (isset($errinfo['exception']) && $errinfo['exception'] instanceof \PDOException) {
			$errinfo['email'] = true;
		}

		self::logToFile($errinfo);
		unset($errinfo['exception']);

		if (isset($GLOBALS['DP_CRON_LOGGER'])) {
			$GLOBALS['DP_CRON_LOGGER']->log("ERROR {$errinfo['session_name']}: {$errinfo['summary']}", 'ERR', array('flag' => 'job_error'));
		}

		if (class_exists('\Application\DeskPRO\App') && !\Application\DeskPRO\App::getConfig('debug.no_report_errors')) {
			if (!(isset($errinfo['no_send_error']) && $errinfo['no_send_error'])) {
				\Application\DeskPRO\Service\ErrorReporter::reportPhpError($errinfo);
			}
		}

		self::$is_logging = false;
	}


	/**
	 * Logs error info to the data/error.log file and the summary to the PHP error log.
	 *
	 * @param array $errinfo
	 */
	public static function logToFile(array $errinfo)
	{
		self::$wrote_log_file = false;

		$str = array();
		if ($errinfo['type'] == 'exception') {
			$e = $errinfo['exception'];
			$line = sprintf("DeskPRO Exception: %s:%s (%s line %s): %s", $errinfo['exception_type'], $e->getCode(), $errinfo['errfile'], $errinfo['errline'], $e->getMessage());
			$str[] = sprintf("Exception: %s %s\n", $e->getCode(), $e->getMessage());
			$str[] = sprintf("\tType: %s\n", $errinfo['exception_type']);
			$str[] = sprintf("\tDate: %s\n", date('Y-m-d H:i:s'));
			$str[] = sprintf("\tBuild: %s\n", defined('DP_BUILD_NUM') ? DP_BUILD_NUM : defined('DP_BUILD_TIME') ? DP_BUILD_TIME : '0');
			$str[] = sprintf("\tLine %d of %s\n", $errinfo['errline'], $errinfo['errfile']);
		} else {
			$line = sprintf("DeskPRO Error: %s (%s line %s): %s", $errinfo['errname'], $errinfo['errfile'], $errinfo['errline'], $errinfo['errstr']);
			$str[] = sprintf("Error: %s\n", $errinfo['errstr']);
			$str[] = sprintf("\tType: %s\n", $errinfo['errname']);
			$str[] = sprintf("\tDate: %s\n", date('Y-m-d H:i:s'));
			$str[] = sprintf("\tBuild: %s\n", defined('DP_BUILD_NUM') ? DP_BUILD_NUM : defined('DP_BUILD_TIME') ? DP_BUILD_TIME : '0');
			$str[] = sprintf("\tLine %d of %s\n", $errinfo['errline'], $errinfo['errfile']);
		}

		$errinfo['trace'] = trim($errinfo['trace']);
		if ($errinfo['trace']) {
			$lines = explode("\n", $errinfo['trace']);
			foreach ($lines as $l) {
				$str[] = sprintf("\t-> %s\n", trim($l));
			}
		}

		if (!empty($errinfo['context_data'])) {
			$str[] = "Context Data:\n";
			$str[] = $errinfo['context_data'];
			$str[] = "\n\n";
		}

		$str = trim(implode('', $str));
		$str .= "\n";

		// Prefix each line for easier parsing
		$str = preg_replace('#^#m', "<DP_LOG:{$errinfo['session_name']}> ", $str);
		$line = preg_replace('#^#m', "<DP_LOG:{$errinfo['session_name']}> ", $line);

		// Always write error line to standard error log
		@error_log($line, 0);

		if (function_exists('dp_get_log_dir') && dp_get_log_dir() && ($fh = @fopen(dp_get_log_dir() . '/error.log', 'a')) !== false) {

			$written = @fwrite($fh, $str);
			@fclose($fh);

			if ($written) {
				self::$wrote_log_file = dp_get_log_dir() . '/error.log';

				// Max 15MB
				if (filesize(self::$wrote_log_file) > 15728640) {
					$file = @file_get_contents(self::$wrote_log_file);
					if ($file) {
						$file = substr($file, -15728640);
						@file_put_contents(self::$wrote_log_file, $file);
						$file = null;
					}
				}
			}
		}

		if (
			isset($errinfo['email'])
			&& $errinfo['email']
			&& defined('DP_TECHNICAL_EMAIL')
			&& DP_TECHNICAL_EMAIL
			&& !isset($GLOBALS['DP_CONFIG']['debug']['no_report_errors'])
			&& function_exists('dp_should_throttle_action')
			&& !dp_should_throttle_action('email_error', 300)
		) {

			if (isset($errinfo['exception']) && $errinfo['exception'] instanceof \PDOException) {
				$line = "There has been a MySQL error: " . $errinfo['exception']->getMessage();
			}

			$fallback_send = true;

			if (class_exists('Application\DeskPRO\App')) {
				try {
					$message = App::getMailer()->createMessage();
					$message->setTo(DP_TECHNICAL_EMAIL);
					$message->setSubject($line);
					$message->setBody($str, 'text/plain');
					App::getMailer()->send($message);
					$fallback_send = false;
				} catch (\Exception $e) {}
			}

			if ($fallback_send) {
				@mail(DP_TECHNICAL_EMAIL, $line, $str);
			}
		}
	}

	/**
	 * Gets a standard error info array from an exception.
	 *
	 * @param \Exception $exception
	 * @return array
	 */
	public static function getExceptionInfo(\Exception $exception)
	{
		$errno   = $exception->getCode();
		$errstr  = self::stripPathPrefix($exception->getMessage());
		$errfile = self::stripPathPrefix($exception->getFile());
		$errline = $exception->getLine();

		$backtrace = $exception->getTrace();
		$trace = self::formatBacktrace($backtrace);
		$context_data = '';

		if (isset($exception->_dp_query)) {
			$errstr .= ' -- Query: ' . substr($exception->_dp_query, 0, 2000);

			if (!empty($exception->_dp_query_params)) {
				$context_data = self::varToString($exception->_dp_query_params);
			}
		}

		$type = get_class($exception);
		$summary = "[EXCEPTION] $type:$errno $errstr ($errfile:$errline)";

		$display = true;
		if (!(error_reporting() & E_ERROR)) {
			$display = false;
		}

		$prev = $exception->getPrevious();
		if ($prev) {
			$previnfo = self::getExceptionInfo($prev);
			$summary .= ", " . $previnfo['summary'];
			$trace .= "\n\n(Alt Exception)\n" . $previnfo['trace'];
		}

		$errinfo = array(
			'type'           => 'exception',
			'session_name'   => isset($exception->_dp_sn) ? $exception->_dp_sn : self::genSessionName(),
			'exception'      => $exception,
			'exception_type' => get_class($exception),
			'die'            => true,
			'pri'            => 'ERR',
			'trace'          => $trace,
			'summary'        => $summary,
			'errstr'         => $errstr,
			'errname'        => 'EXCEPTION',
			'errno'          => $errno,
			'errfile'        => $errfile,
			'errline'        => $errline,
			'display'        => $display,
			'build'          => DP_BUILD_TIME,
			'process_log'    => implode("\n", self::$process_log),
			'context_data'   => $context_data,
			'error_time'     => microtime(true),
			'time_to_error'  => defined('DP_START_TIME') ? sprintf("%0.4f", microtime(true) - DP_START_TIME) : 0
		);

		if (self::isNoReportException($exception)) {
			$errinfo['no_send_error'] = true;
		}

		return $errinfo;
	}


	/**
	 * @param \Exception $exception
	 * @return bool
	 */
	public static function isNoReportException(\Exception $exception)
	{
		static $ignore = array(
			'Swift_TransportException',
			'Swift_IoException',
			'Zend\\Mail\\Protocol\\Exception\\RuntimeException',
		);

		foreach ($ignore as $cls) {
			if ($exception instanceof $cls) {
				return true;
			}
		}

		if ($exception instanceof \Doctrine\DBAL\Types\ConversionException && strpos($exception->getMessage(), 'Doctrine Type array') !== false) {
			return true;
		}

		return false;
	}


	/**
	 * Gets a standard error info array from an error.
	 *
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param string $errline
	 * @return array
	 */
	public static function getErrorInfo($errno, $errstr, $errfile, $errline)
	{
		$die = false;
		switch ($errno) {
			case E_ERROR:
				$die = true;
				$pri = 'ERR';
				$errname = "E_ERROR";
				break;

			case E_WARNING:
			case E_USER_WARNING:
				$pri = 'WARN';
				$errname = "E_WARNING";
				break;

			case E_NOTICE:
			case E_USER_NOTICE:
				$pri = 'NOTICE';
				$errname = "E_NOTICE";
				break;

			case E_STRICT:
				$pri = 'STRICT';
				$errname = "E_STRICT";
				break;

			case E_RECOVERABLE_ERROR:
				$pri = 'ERR';
				$errname = "E_RECOVERABLE_ERROR";
				break;

			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$pri = 'NOTICE';
				$errname = "E_DEPRECATED";
				break;

			default:
				$pri = 'ERR';
				$errname = 'UNKNOWN';
		}

		$context_data = '';

		$display = true;
		$no_send_error = false;
		if (!(error_reporting() & $errno)) {
			$display = false;
		}

		// Dont output apc warnings (but still log them)
		if ($display && strpos($errstr, 'Unable to allocate memory for pool') !== false) {
			$display = false;
			$no_send_error = true;
		}

		$errstr  = self::stripPathPrefix($errstr);
		$errfile = self::stripPathPrefix($errfile);

		$backtrace = debug_backtrace();
		$trace = self::formatBacktrace($backtrace);

		$summary = "[$errname:$errno] $errstr ($errfile:$errline)";

		return array(
			'type'            => 'error',
			'session_name'    => self::genSessionName(),
			'die'             => $die,
			'pri'             => $pri,
			'trace'           => $trace,
			'summary'         => $summary,
			'errstr'          => $errstr,
			'errname'         => $errname,
			'errno'           => $errno,
			'errfile'         => $errfile,
			'errline'         => $errline,
			'display'         => $display,
			'build'           => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0,
			'process_log'     => implode("\n", self::$process_log),
			'context_data'    => $context_data,
			'error_time'     => microtime(true),
			'time_to_error'  => defined('DP_START_TIME') ? sprintf("%0.4f", microtime(true) - DP_START_TIME) : 0,
			'no_send_error'  => $no_send_error,
		);
	}


	/**
	 * Strips the full path prefix from $content. This makes all paths relative to the root of DeskRPO install.
	 *
	 * @param string $content
	 * @return string
	 */
	public static function stripPathPrefix($content)
	{
		$content = str_replace('\\', '/', $content);

		$prefix = str_replace('\\', '/', DP_ROOT) . '/';
		$content = str_replace($prefix, '/app/', $content);

		$prefix = str_replace('\\', '/', DP_WEB_ROOT) . '/';
		$content = str_replace($prefix, '/', $content);

		return $content;
	}


	/**
	 * Formats a backtrace.
	 *
	 * @param array $backtrace
	 * @return string
	 */
	public static function formatBacktrace(array $backtrace)
	{
		$trace = '';

		foreach($backtrace as $k=>$v){

			$prefix = "#$k ";
			$line = '';

			if (!empty($v['file'])) {
				$v['file'] = self::stripPathPrefix($v['file']);
				$prefix .= "[{$v['file']}:{$v['line']}] ";
			}

			if (isset($v['object'])) {
				$line .= get_class($v['object']) . "::";
			} elseif (isset($v['class'])) {
				$line .= $v['class'] . "::";
			}

			$line .= "{$v['function']}(";

			if (!empty($v['args'])) {
				$line .= self::varToString($v['args']);
			}

			$line .= ")";

			$trace .= $prefix . ' ' . trim($line) . "\n";
		}

		$trace = preg_replace('#PDO::__construct(.*?)$#m', 'PDO::__construct(...)', $trace);

		return trim($trace);
	}


	/**
	 * Used with formatBacktrace to format an array (usually parameters) to a string, being sure not to recurse
	 * too deep.
	 *
	 * @param mixed $var
	 * @param int $_depth
	 * @return string
	 */
	public static function varToString($var, $_depth = 0)
    {
        if (is_object($var)) {
            return sprintf('[object](%s)', get_class($var));
        }
        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
				if ($_depth > 8) {
					$a[] = sprintf('%s => %s', $k, '(string)');
				} else {
					$a[] = sprintf('%s => %s', $k, self::varToString($v, $_depth+1));
				}
            }
            return sprintf("[array](%s)", implode(', ', $a));
        }
        if (is_resource($var)) {
            return '[resource]';
        }
		$str = (string)$var;
		if (strlen($str) > 1000) {
			$str = substr($str, 0, 1000) . "...(clipped)";
		}
        return str_replace("\n", '', var_export(self::stripPathPrefix($str), true));
    }
}