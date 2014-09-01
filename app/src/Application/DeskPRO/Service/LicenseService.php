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
use Guzzle\Http\Client as HttpClient;

class LicenseService
{
	/**
	 * @return array
	 */
	public static function getLatestVersion()
	{
		static $latest = null;

		if ($latest === null) {
			$latest = self::fetchServiceResult('check-latest-version.json', array('my_build' => DP_BUILD_TIME));
		}

		return $latest;
	}


	/**
	 * Compares current build to the latest build available.
	 *
	 * Data returned:
	 * - build: <timestamp>
	 * - build_link: <url>
	 * - your_build: <timestamp>
	 * - count_behind: <int>
	 *
	 * @return array
	 */
	public static function compareVersion()
	{
		static $data = null;

		if ($data === null) {
			try {
				$data = self::fetchServiceResult('build/compare-version.json', array('my_build' => DP_BUILD_TIME));
			} catch (\Exception $e) {
				$data = array();
			}
		}

		return $data;
	}


	/**
	 * Get version notice info
	 *
	 * Data returned:
	 * - link: <url>
	 * - message: <text>
	 * - level: notice/warning/critical
	 *
	 * @return array
	 */
	public static function getVersionNotices()
	{
		static $data = null;

		if ($data === null) {
			try {
				$data = self::fetchServiceResult('build/version-notices.json', array('my_build' => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0));
			} catch (\Exception $e) {
				$data = array();
			}
		}

		return $data;
	}


	/**
	 * Gets news from RSS feed
	 *
	 * @return array|null
	 */
	public static function getNews()
	{
		$news = array();

		try {
			$client = new HttpClient(\DeskPRO\Kernel\License::getSupportUrl(), array(
				'ssl.certificate_authority' => false
			));
			$request = $client->get('/news/2-product.rss');
			$response = $request->send();

			if (!$response->isSuccessful()) {
				return null;
			}

			$rss = simplexml_load_string($response->getBody(true));
			unset($r);

			$x = 0;
			foreach ($rss->channel->item as $item) {
				$news[] = array(
					'title' => (string)$item->title,
					'link'  => (string)$item->link
				);
				if ($x++ > 5) {
					break;
				}
			}
		} catch (\Exception $e) {
			return null;
		}

		return $news;
	}


	/**
	 * @param string $endpoint
	 * @param array $post_data
	 * @return array
	 */
	public static function fetchServiceResult($endpoint, array $post_data = array())
	{
		$url = \DeskPRO\Kernel\License::getLicServer() . '/api/' . ltrim($endpoint, '/');

		try {
			$client = new \Zend\Http\Client(null, array('timeout' => 8, 'strictredirects' => true));
			$client->setMethod(\Zend\Http\Request::METHOD_POST);
			$client->setUri(\DeskPRO\Kernel\License::getLicServer() . '/api/' . ltrim($endpoint, '/'));
			$client->getRequest()->getPost()->fromArray($post_data);
			$r = $client->send();
			$result = $r->getBody();
		} catch (\Exception $e) {
			$result = '';
		}

		if (!$result) {
			throw new \RuntimeException("No response from server: $url $result");
		}

		$res_data = json_decode($result, true);
		if (!is_array($res_data)) {
			throw new \RuntimeException("Invalid JSON response from server: $url $result");
		}

		return $res_data;
	}
}