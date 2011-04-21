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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class Boot
{
	public static function bootWeb($env = 'prod', $debug = false, Request $request = null)
	{
		if ($request === null) {
			$request = Request::createfromGlobals();
		}

		$kernel_class = 'DeskPRO\\Kernel\\UserKernel';

		if (preg_match('#^/(agent|admin|api|dev)/#', $request->getPathInfo())) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
		}

		$kernel = new $kernel_class($env, $debug);
		$kernel->handle($request)->send();
	}

	public static function bootCli($env = 'prod', $debug = false)
	{
		$kernel = new \DeskPRO\Kernel\CliKernel($env, $debug);

		$application = new Application($kernel);
		$application->run();
	}
}