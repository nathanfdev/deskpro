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
 * @subpackage PageDisplay
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
