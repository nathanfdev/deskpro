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

use \Application\DeskPRO\App;

use \Orb\Log\Logger;

use \Symfony\Component\EventDispatcher\EventInterface;
use \Symfony\Component\HttpKernel\Log\LoggerInterface;
use \Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use \Symfony\Component\HttpKernel\HttpKernelInterface;
use \Symfony\Component\HttpKernel\Exception\FlattenException;
use \Symfony\Component\HttpFoundation\Request;

class ExceptionListener extends \Symfony\Component\HttpKernel\Debug\ExceptionListener
{
	public function __construct($controller, LoggerInterface $logger = null)
    {
        $this->controller = $controller;
        $this->logger = $logger;

		set_error_handler(array($this, 'handleError'), E_ALL | E_STRICT);
    }

	public function handle(EventInterface $event)
	{
		static $handling;

        if ($handling === true) return false;
        $handling = true;

        $exception = $event->get('exception');
        $request = $event->get('request');

		error_log(sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine()));
		$this->_logException($exception);

        $attributes = array(
            '_controller' => $this->controller,
            'exception'   => FlattenException::create($exception),

            // when using CLI, we force the format to be TXT
            'format'      => 0 === strncasecmp(PHP_SAPI, 'cli', 3) ? 'txt' : $request->getRequestFormat(),
        );

        $request = $request->duplicate(null, null, $attributes);

        try {
            $response = $event->getSubject()->handle($request, HttpKernelInterface::SUB_REQUEST, true);
        } catch (\Exception $e) {
            $message = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage());
            error_log($message);

            // re-throw the exception as this is a catch-all
            throw $exception;
        }

        $event->setProcessed();

        $handling = false;

        return $response;
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

		try {
			$logger = App::createNewLogger('error_log', null);
			$logger->log($summary, 3, array(
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

		//ob_start();
		//debug_print_backtrace();
		//$trace = ob_get_clean();
		$trace = '';

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
			echo "\n";
			echo $trace;
		}

		$handling = false;

		if ($die) {
			exit;
		}

		return true;
	}
}