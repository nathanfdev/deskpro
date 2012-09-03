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

namespace DeskPRO\Kernel;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

use Application\DeskPRO\App;

class UserKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\AgentBundle\AgentBundle(), // so templates can work when notiying
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/user/config_'.$this->getEnvironment().'.php');
	}


	public function preResponseHandled(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		// Run parent first for correct index.php/no-index.php redirection
		$response = parent::preResponseHandled($request, $type, $catch);
		if ($response) {
			return $response;
		}

		$is_installed = App::getSetting('core.setup_initial');
		if (!$is_installed) {
			return null;
		}

		$redirect_corrections = App::getSetting('core.redirect_correct_url');
		if (!$redirect_corrections) {
			return null;
		}

		$now_path = $request->getPathInfo();
		if (strpos($request->getRequestUri(), '/index.php/') !== false) {
			$now_path = '/index.php' . $now_path;
		}

		$urlinfo        = parse_url(App::getSetting('core.deskpro_url'));
		if (!$urlinfo || empty($urlinfo['host']) || empty($urlinfo['scheme'])) {
			return null;
		}
		$now_host       = strtolower($urlinfo['host']);
		$now_scheme     = strtolower($urlinfo['scheme']);
		$correct_host   = strtolower($request->getHttpHost());
		$correct_scheme = strtolower($request->getScheme());

		$do_correction = false;
		if ($correct_scheme == 'https' && $now_scheme != 'https') {
			$do_correction = true;
		} elseif ($now_host != $correct_host) {
			$do_correction = true;
		}

		if ($do_correction) {
			$url = App::getSetting('core.deskpro_url') . ltrim($now_path, '/');
			$response = new RedirectResponse($url, 301);
			return $response;
		}

		return null;
	}
}