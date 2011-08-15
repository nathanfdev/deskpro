<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage HttpKernel
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\HttpKernel;

use Application\DeskPRO\App;

use Orb\Log\Logger;
use Orb\Util\Strings;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
	protected $last_exception = null;

	public function getLastException()
	{
		return $this->last_exception;
	}

	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		static $handling;

		if ($handling === true) return false;
		$handling = true;

		$exception = $event->getException();
		$this->_logException($exception);

		$handling = false;
	}

	protected function _logException(\Exception $exception)
	{
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
	}

	public function _stripPathPrefix($content)
	{
		$prefix = DP_ROOT . '/';

		$content = str_replace($prefix, '', $content);

		return $content;
	}

	public function handleError($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) return true;

		static $handling;

		if ($handling === true) return false;
		$handling = true;

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

		$handling = false;

		if ($die) {
			exit;
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