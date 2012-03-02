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
 * @subpackage HttpKernel
 */

namespace Application\DeskPRO\HttpKernel;

use Application\DeskPRO\App;

use Orb\Log\Logger;
use Orb\Util\Strings;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
	protected $last_exception = null;
	private $handling_exception = false;

	public function getLastException()
	{
		return $this->last_exception;
	}

	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		if ($this->handling_exception === true) return;
		$this->handling_exception = true;

		$exception = $event->getException();
		$this->_logException($exception);

		$this->handling_exception = false;
	}

	protected function _logException(\Exception $exception)
	{
		if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
			$this->_log404($exception);
			return;
		}

		$errno   = $exception->getCode();
		$errstr  = $exception->getMessage();
		$errfile = $this->_stripPathPrefix($exception->getFile());
		$errline = $exception->getLine();

		$errname = get_class($exception);
		$summary = "[$errname:$errno] $errstr ($errfile:$errline)";

		$trace = $this->getTraceString($exception->getTrace());
		$trace = $this->_stripPathPrefix($trace);

		$exception->_dp_sn = Strings::random(8, Strings::CHARS_KEY);

		// This is fetched from the template
		$this->last_exception = $exception;

		try {
			$logger = App::createNewLogger('error_log', null);
			$logger->log($summary, 3, array(
				'session_name' => $exception->_dp_sn,
				'trace' => $trace,
				'class' => get_class($exception),
				'file' => $exception->getFile(),
				'line' => $exception->getLine()
			));
		} catch (\Exception $e) {}

		$trace_short = $exception->getTraceAsString();
		$trace_short = str_replace(DP_ROOT, '/', $trace_short);

		error_log($summary . " $trace_short", 0);

		if (App::getConfig('debug.email_on_error')) {
			try {
				$message = App::getMailer()->createMessage();
				$message->setTo(App::getConfig('debug.email_on_error'));
				$message->setSubject("[DeskPRO Error] $summary");
				$message->setBody(print_r(array(
					'session_name' => $exception->_dp_sn,
					'trace' => $trace,
					'class' => get_class($exception),
					'file' => $exception->getFile(),
					'line' => $exception->getLine()
				), true));

				App::getMailer()->send($message);
			} catch (\Exception $e) {}
		}

		if (in_array(ini_get('display_errors'), array(1, '1', 'on', 'On', true))) {
			echo $summary;
		}

		// Unhandled exceptions in CLI means we should exit
		if (isset($GLOBALS['DP_IS_IN_CLI'])) {

			echo "\n";
			echo $trace;
			echo "\n";

			$code = $exception->getCode();
			if (is_numeric($code)) {
				$code = (int)$code;
			}

			if ($code > 255) $code = 255;
			if ($code == 0) $code = 1;
			exit($code);
		}
	}

	public function _log404(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception)
	{
		$summary = $exception->getMessage();

		$trace = $this->getTraceString($exception->getTrace());
		$trace = $this->_stripPathPrefix($trace);

		$exception->_dp_sn = Strings::random(8, Strings::CHARS_KEY);

		// This is fetched from the template
		$this->last_exception = $exception;

		try {
			$logger = App::createNewLogger('error_not_found', null);
			$logger->log($summary, 3, array(
				'session_name' => $exception->_dp_sn,
				'trace' => $trace,
				'class' => get_class($exception),
				'file' => $exception->getFile(),
				'line' => $exception->getLine()
			));
		} catch (\Exception $e) {}
	}

	public function _stripPathPrefix($content)
	{
		$prefix = DP_ROOT . '/';

		$content = str_replace($prefix, '', $content);

		return $content;
	}

	public function handleException(\Exception $exception)
	{
		$this->_logException($exception);
	}

	public function handleError($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) return true;

		if ($this->handling_exception === true) return true;
		$this->handling_exception = true;

		$die = false;

		switch ($errno) {
			case E_ERROR:
				$die = true;
				$pri = Logger::ERR;
				$errname = "E_ERROR";
				break;

			case E_WARNING:
			case E_USER_WARNING:
				$pri = Logger::WARN;
				$errname = "E_WARNING";
				break;

			case E_NOTICE:
			case E_USER_NOTICE:
				$pri = Logger::NOTICE;
				$errname = "E_NOTICE";
				break;

			case E_STRICT:
				$pri = Logger::NOTICE;
				$errname = "E_STRICT";
				break;

			case E_RECOVERABLE_ERROR:
				$pri = Logger::ERR;
				$errname = "E_RECOVERABLE_ERROR";
				break;

			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$pri = Logger::NOTICE;
				$errname = "E_DEPRECATED";
				break;
		}

		$errfile = $this->_stripPathPrefix($errfile);

		$trace = $this->getTraceString(debug_backtrace());
		$trace = $this->_stripPathPrefix($trace);

		$dpsn = Strings::random(8, Strings::CHARS_KEY);
		$summary = "[$errname:$errno] $errstr ($errfile:$errline) [SN$dpsn]";

		try {
			$logger = App::createNewLogger('error_log', null);
			$logger->log($summary, $pri, array(
				'session_name' => $dpsn,
				'trace' => $trace,
				'errno' => $errno,
				'errname' => $errname,
				'file' => $errfile,
				'line' => $errline
			));
		} catch (\Exception $e) {}

		if (in_array(ini_get('display_errors'), array(1, '1', 'on', 'On', true))) {
			echo $summary;
		}

		error_log($summary, 0);

		$this->handling_exception = false;

		if ($die) {
			exit($errno);
		}

		return true;
	}

	private function getTraceString(array $trace_array)
	{
		$trace = '';
		foreach($trace_array as $k=>$v){

			$line = "#$k ";

			if (isset($v['object'])) {
				$line .= get_class($v['object']) . "::";
			} elseif (isset($v['class'])) {
				$line .= $v['class'] . "::";
			}

			$line .= "{$v['function']}(";

			if (!empty($v['args'])) {
				$line .= $this->varToString($v['args']);
			}

			$line .= ")";

			if (!empty($v['file'])) {
				$line .= " called at [{$v['file']}:{$v['line']}]";
			}

			$line .= "\n";

			$trace .= $line;
		}

		return $trace;
	}

	private function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('[object](%s)', get_class($var));
        }
        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, $this->varToString($v));
            }
            return sprintf("[array](%s)", implode(', ', $a));
        }
        if (is_resource($var)) {
            return '[resource]';
        }
        return str_replace("\n", '', var_export((string) $var, true));
    }
}
