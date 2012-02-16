<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage PageDisplay
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\PageDisplay\Item\Portal;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\PortalPageDisplay;

use Orb\Util\Strings;

class Twitter extends Feed
{
	public function getCacheOptions()
	{
		return array(
			'lifetime' => 43200, /*1 hour*/
			'force_cache' => true
		);
	}

	protected function init()
	{
		$this->setOption('tpl', 'UserBundle:Portal:twitter-sidebar.html.twig');
		$this->setOption('feed_url', 'http://twitter.com/statuses/user_timeline/' . $this->getOption('twitter_name') . '.rss');

		if (!$this->getOption('max_items')) {
			$this->setOption('max_items', 5);
		}
	}

	public function getTplVars()
	{
		return array(
			'twitter_name' => 'deskpro',
		);
	}

	protected function processItems(array $feed_items)
	{
		foreach ($feed_items as &$item) {
			$item['description'] = $this->parseText($item['description']);
		}

		return $feed_items;
	}

	protected function parseText($text)
	{
		$text = preg_replace('#^(.*?):#', '', $text);
		$text = Strings::autoLink($text);
		$text = preg_replace('#(^|\W)(@([a-zA-Z0-9]+))(\W|$)#', '$1<a href="http://twitter.com/$3">$2</a>$4', $text);

		return $text;
	}
}