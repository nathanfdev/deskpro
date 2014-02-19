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

use Orb\Util\Strings;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

use Application\DeskPRO\App;

class ApiKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\ApiBundle\ApiBundle(),
			new \Application\AgentBundle\AgentBundle(),
			new \Application\ReportsInterfaceBundle\ReportsInterfaceBundle(),
		);

		if (defined('DPC_IS_CLOUD')) {
			$bundles[] = new \Cloud\ApiBundle\CloudApiBundle();
		}

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		if (defined('DPC_IS_CLOUD')) {
			$loader->load(DP_ROOT.'/sys/config-cloud/api/config_'.$this->getEnvironment().'.php');
		} else {
			$loader->load(DP_ROOT.'/sys/config/api/config_'.$this->getEnvironment().'.php');
		}
	}

	protected  function postResponseHandled($response, $request)
	{
		if (!$response || !$request || !function_exists('dp_get_config')) {
			return parent::postResponseHandled($response, $request);
		}

		if (dp_get_config('enable_api_log')) {
			if (dp_get_config('enable_api_log') === true || dp_get_config('enable_api_log') === "1" || dp_get_config('enable_api_log') === 1 || dp_get_config('enable_api_log') === "true") {
				$path = dp_get_log_dir() . '/api.log';
			} else {
				$path = dp_get_config('enable_api_log');
			}

			$line1 = sprintf("[%s] %s %s\n", date('Y-m-d H:i:s"'), $request->getMethod(), $request->getUri());
			$sent = '';
			$return = '';

			$post = $_POST;
			if (in_array($request->getContentType(), array('application/json', 'text/x-json'))) {
				$json_post = @json_decode(@file_get_contents('php://input'), true);
				if ($json_post) {
					$post = array_merge($post, $json_post);
				}
			}

			if ($post) {
				$post_info = print_r($post, true);
				$post_info = Strings::modifyLines($post_info, "\t");
				$sent = "\t<POST> " . $request->getContentType() . "\n$post_info\n";
			}

			$ret_data = $response->getContent();
			$ret_type = $response->headers->get('Content-Type');
			$ret_code = $response->getStatusCode();

			if ($ret_type == 'application/json') {
				$json = @json_decode($ret_data, true);
				if($json) {
					$ret_data = print_r($json, true);
				}
			}

			$ret_data = Strings::modifyLines($ret_data, "\t");

			$return = "\t<RETURN> $ret_code $ret_type\n$ret_data";

			$log_content = trim(implode("", array($line1, $sent, $return))) . "\n\n\n";

			file_put_contents($path, $log_content, FILE_APPEND);
			@chmod($path, 0777);
		}

		return parent::postResponseHandled($response, $request);
	}
}