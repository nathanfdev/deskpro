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

namespace Application\DeskPRO\Command;

use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Util\Strings;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Route;

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dp:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		require(DP_ROOT.'/MockRoute.php');
		$files = array(
			DP_ROOT.'/src/Application/AdminBundle/Resources/config/admin-routing.php',
			DP_ROOT.'/src/Application/AdminInterfaceBundle/Resources/config/admin-interface-routing.php',
			DP_ROOT.'/src/Application/AgentBundle/Resources/config/agent-routing.php',
			DP_ROOT.'/src/Application/ApiBundle/Resources/config/api-routing.php',
			DP_ROOT.'/src/Application/BillingBundle/Resources/config/billing-routing.php',
			DP_ROOT.'/src/Application/DeskPRO/Resources/config/dp-routing.php',
			DP_ROOT.'/src/Application/InstallBundle/Resources/config/install-routing.php',
			DP_ROOT.'/src/Application/ReportBundle/Resources/config/reports-routing.php',
			DP_ROOT.'/src/Application/ReportsInterfaceBundle/Resources/config/reports-interface-routing.php',
			DP_ROOT.'/src/Application/UserBundle/Resources/config/user-routing.php',
		);

		foreach ($files as $f) {
			$contents = file_get_contents($f);
			$contents = str_replace('use Symfony\Component\Routing\Route;', 'use MockRoute as Route;', $contents);
			file_put_contents($f, $contents);

			/** @var \Symfony\Component\Routing\RouteCollection $collection */
			$collection = require($f);

			$output = array();

			foreach ($collection as $name => $route) {
				if ($route->arg3 != -1) {
					$arg3 = $route->arg3;
					$method = null;
					if (isset($arg3['_method'])) {
						$method = $arg3['_method'];
						unset($route->arg3['_method']);

						if ($route->arg7 == -1) {
							$route->arg7 = array();
						}

						$route->arg7[] = $method;
					}
				}

				$info = array('path' => $route->arg1);
				if ($route->arg2 != -1 && $route->arg2) {
					if (isset($route->arg2['_controller'])) {
						$info['controller'] = $route->arg2['_controller'];
						unset($route->arg2['_controller']);
					}
					if ($route->arg2 != -1 && $route->arg2) {
						$info['defaults'] = $route->arg2;
					}
				}
				if ($route->arg3 != -1 && $route->arg3) {
					$info['requirements'] = $route->arg3;
				}
				if ($route->arg4 != -1 && $route->arg4) {
					$info['options'] = $route->arg4;
				}
				if ($route->arg5 != -1 && $route->arg5) {
					$info['host'] = $route->arg5;
				}
				if ($route->arg6 != -1 && $route->arg6) {
					$info['schemes'] = $route->arg6;
				}
				if ($route->arg7 != -1 && $route->arg7) {
					$info['methods'] = $route->arg7;
				}

				$block = "\$collection->create('$name', " . Arrays::prettyDump($info) . ");";

				$output[] = $block;
			}

			$output = implode("\n\n", $output);
			$output = "<?php if (!defined('DP_ROOT')) exit('No access');\n\n"
				. "require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');\n"
				. "require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');\n\n"
				. "use Application\DeskPRO\Routing\RouteCollection;\n"
				. "use Application\DeskPRO\Routing\Route;\n\n"
				. "\$collection = new RouteCollection();\n\n"
				. $output
				. "\n\n"
				. "return \$collection;"
				. "\n";

			file_put_contents($f, $output);
		}
	}
}
