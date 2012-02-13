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
	}

	public function onCoreException(GetResponseForExceptionEvent $event)
	{
		static $handling;

		if ($handling === true) return;
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
		$exception->_dp_sn = Strings::random(8, Strings::CHARS_KEY);

		$errinfo = \DeskPRO\Kernel\KernelErrorHandler::getExceptionInfo($exception);
		\DeskPRO\Kernel\KernelErrorHandler::logToFile($errinfo);
	}
}
