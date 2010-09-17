<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Scraper
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Scraper;

/**
 * Scrapes a users twitter feed. A user must have a public feed for this to work.
 */
class TwitterFeed
{
	/**
	 * @param int $user_id The users ID
	 * @return ItemInterface
	 */
	function getData($user_id)
	{
		$twitter_url = 'http://twitter.com/statuses/user_timeline/' . $user_id . '.json';

		$data = file_get_contents($url);
		$data = json_decode($data, true);

		$userinfo = null;
		$tweets = array();

		foreach ($data as $item) {
			if ($userinfo === null) {
				$userinfo = $item['user'];
			}
			unset($item['user']);
			$tweers[] = $item;
		}

		$item = new \Orb\Scraper\Item(
			$userinfo['id'],
			$userinfo['screen_name'],
			array('userinfo' => $userinfo, 'tweets' => $tweets)
		);

		return $item;
	}
}