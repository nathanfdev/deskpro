<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Debug;

use Application\DeskPRO\App;

use Orb\Log\Logger;
use Orb\Util\Strings;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Request;

class ExceptionListener extends \Symfony\Component\HttpKernel\Debug\ExceptionListener
{
	public function __construct($controller, LoggerInterface $logger = null)
	{
		$this->controller = $controller;
		$this->logger = $logger;

		set_error_handler(array($this, 'handleError'), E_ALL | E_STRICT);
	}

	public function onCoreException(GetResponseForExceptionEvent $event)
	{
		static $handling;

		if ($handling === true) return false;
		$handling = true;

		$exception = $event->getException();
		$request = $event->getRequest();

		if (null !== $this->logger) {
			$this->logger->err(sprintf('%s: %s (uncaught exception)', get_class($exception), $exception->getMessage()));
		} else {
			error_log(sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine()));
		}

		$this->_logException($exception);

		$logger = null !== $this->logger ? $this->logger->getDebugLogger() : null;
		$flattenException = FlattenException::create($exception);
		if ($exception instanceof HttpExceptionInterface) {
			$flattenException->setStatusCode($exception->getStatusCode());
			$flattenException->setHeaders($exception->getHeaders());
		}

		$flattenException->_dp_sn = $exception->_dp_sn;

		$attributes = array(
			'_controller' => $this->controller,
			'exception'   => $flattenException,
			'logger'      => $logger,
			// when using CLI, we force the format to be TXT
			'format'      => 0 === strncasecmp(PHP_SAPI, 'cli', 3) ? 'txt' : $request->getRequestFormat(),
		);

		$request = $request->duplicate(null, null, $attributes);

		try {
			$response = $event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST, true);
		} catch (\Exception $e) {
			$message = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage());
			if (null !== $this->logger) {
				$this->logger->err($message);
			} else {
				error_log($message);
			}

			// set handling to false otherwise it wont be able to handle further more
			$handling = false;

			// re-throw the exception as this is a catch-all
			throw $exception;
		}

		$event->setResponse($response);

		$handling = false;
	}

	protected function _logException(\Exception $exception)
	{
		$errno = $exception->getCode();
		$errstr = $exception->getMessage();
		$errfile = $this->_stripPathPrefix($exception->getFile());
		$errline = $exception->getLine();

		$errname = get_class($exception);
		$summary = "[$errname:$errno] $errstr ($errfile:$errline)";
		$trace = $this->_stripPathPrefix($exception->getTraceAsString());

		$exception->_dp_sn = Strings::random(8, Strings::CHARS_KEY);

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

		$trace = '';
		foreach(debug_backtrace() as $k=>$v){

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

		$trace = $this->_stripPathPrefix($trace);

		$summary = "[$errname:$errno] $errstr ($errfile:$errline)";

		try {
			$logger = App::createNewLogger('error_log', null);
			$logger->log($summary, $pri, array(
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
