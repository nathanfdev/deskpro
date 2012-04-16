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
 * @subpackage
 */

namespace Application\DeskPRO\Service;
use Application\DeskPRO\App;

class ErrorReporter
{
	public static function getBasicData()
	{
		try {
			$db = App::getDb();
		} catch (\Exception $e) {
			$db = null;
		}
		$stats_fetcher = new \Application\InstallBundle\Data\ServerStats($db);
		$all_stats = $stats_fetcher->getStats();

		$info = array(
			'os' => $all_stats['server_os'],
			'web_server' => isset($all_stats['web_server']) ? $all_stats['web_server'] : '',
			'php_version' => $all_stats['php_version'],
			'mysql_version' => $all_stats['mysql_version'],
			'build' => DP_BUILD_TIME,
		);

		if (defined('DP_INTERFACE')) {
			$url = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
			try {
				$url = App::getRequest()->getUri();
			} catch (\Exception $e) {}
		} else {
			$url = '';
		}

		if (php_sapi_name() == 'cli') {
			$url = implode(' ', $_SERVER['argv']);
		}

		$info['url'] = $url;
		$info['hostname'] = @gethostname();

		if ((defined('DP_INTERFACE') && DP_INTERFACE != 'install') || (!isset($GLOBALS['DP_IS_INSTALL']) || !$GLOBALS['DP_IS_INSTALL'])) {
			try {
				$info['license_id'] = \DeskPRO\Kernel\License::getLicense()->getLicenseId();
			} catch (\Exception $e) {
				$info['license_id'] = '';
			}
		}

		return $info;
	}

	public static function sendReport($service, array $data = array(), $timeout = 5)
	{
		$data = array_merge($data, self::getBasicData());

		try {
			$client = new \Zend\Http\Client(null, array('timeout' => 5));
			$client->setMethod(\Zend\Http\Request::METHOD_POST);
			$client->setUri(\DeskPRO\Kernel\License::getLicServer() . '/' . $service . '.json');
			$client->getRequest()->post()->fromArray($data);
			$r = $client->send();
		} catch (\Exception $e) {}
	}
}