<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Kernel
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace DeskPRO\Kernel;

class Boot
{
	protected static function bootstrap($debug)
	{
		if ($debug) {
			require(DP_ROOT . '/sys/bootstrap-dev.php');
		} else {

			if (!file_exists(DP_ROOT . '/sys/bootstrap.php') || !file_exists((DP_ROOT . '/sys/compiled.php'))) {
				die('You must run the build scripts before you can use the DeskPRO Source in production');
			}

			require(DP_ROOT . '/sys/bootstrap.php');
			require(DP_ROOT . '/sys/compiled.php');
		}
	}

	public static function bootWeb($env = 'prod', $debug = false, \Application\DeskPRO\HttpFoundation\Request $request = null)
	{
		self::bootstrap($debug);

		if ($request === null) {
			$request = \Application\DeskPRO\HttpFoundation\Request::createfromGlobals();
		}

		$path = $request->getPathInfo();

		if (preg_match('#^/agent/#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'agent');
		} elseif (preg_match('#^/admin/#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'admin');
		} elseif (preg_match('#^/report/#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\ReportKernel';
			define('DP_INTERFACE', 'report');
		} elseif (preg_match('#^/api/#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'api');
		} elseif (preg_match('#^/dev/#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'dev');
		} elseif (preg_match('#^/_sys/#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\SysKernel';
			define('DP_INTERFACE', 'sys');
		} else {
			$kernel_class = 'DeskPRO\\Kernel\\UserKernel';
			define('DP_INTERFACE', 'user');
		}

		$kernel = new $kernel_class($env, $debug);
		$kernel->handle($request)->send();
	}

	public static function bootCli($env = 'prod', $debug = false)
	{
		self::bootstrap($debug);

		$kernel = new \DeskPRO\Kernel\CliKernel($env, $debug);

		define('DP_INTERFACE', 'cli');

		$application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
		$application->run();
	}
}
