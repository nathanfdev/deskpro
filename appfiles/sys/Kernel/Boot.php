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

require(DP_ROOT . '/sys/autoload.php');

use Application\DeskPRO\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class Boot
{
	public static function bootWeb($env = 'prod', $debug = false, Request $request = null)
	{
		if ($request === null) {
			$request = Request::createfromGlobals();
		}

		$path = $request->getPathInfo();

		if (preg_match('#^/agent/#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'agent');
		} elseif (preg_match('#^/admin/#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'admin');
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
		$kernel = new \DeskPRO\Kernel\CliKernel($env, $debug);

		define('DP_INTERFACE', 'cli');

		$application = new Application($kernel);
		$application->run();
	}
}
