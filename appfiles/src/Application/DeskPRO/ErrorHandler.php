<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Log\Logger;

class ErrorHandler
{
	public function registerHandler()
	{
		set_error_handler(array($this, 'handleError'), E_ALL | E_STRICT);
	}

	public function handleError($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) return;

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

		$errfile = $this->stripPathPrefix($errfile);

		ob_start();
		debug_print_backtrace();
		$trace = ob_get_clean();

		$trace = $this->stripPathPrefix($trace);

		$summary = "[$errname:$errno] $errstr ($errfile:$errline)";

		try {
			$logger = App::createNewLogger('error_log', null);
			$logger->log($summary, $pri, array('trace' => $trace));
		} catch (\Exception $e) {}

		if (in_array(ini_get('display_errors'), array(1, '1', 'on', 'On', true))) {
			echo $summary;
			echo "\n";
			echo $trace;
		}

		if ($die) {
			exit;
		}

		return true;
	}

	public function stripPathPrefix($content)
	{
		$prefix = DP_ROOT . '/';

		$content = str_replace($prefix, '', $content);

		return $content;
	}
}
