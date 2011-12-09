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

use Zend\Feed\Reader\Reader;
use Application\DeskPRO\App;
use Application\DeskPRO\Entity\PortalPageDisplay;

class Feed extends PortalItemAbstract implements CacheableItem
{
	public function getCacheOptions()
	{
		return array(
			'lifetime' => 43200, /*12 hours*/
			'force_cache' => true
		);
	}

	public function getHtml()
	{
		try {
			$channel = Reader::import($this->getOption('feed_url'));
		} catch (\Exception $e) {
			return '';
		}

		$feed_info = array(
			'title'       => $channel->getTitle(),
			'description' => $channel->getDescription(),
			'link'        => $channel->getLink(),
		);

		$feed_items = array();
		foreach ($channel as $item) {
			$feed_items[] = array(
				'title'       => $item->getTitle(),
				'link'        => $item->getLink(),
				'description' => $item->getDescription(),
				'author'      => $item->getAuthor(),
				'date'        => $item->getDateCreated(),
			);

			if (count($feed_items) >= $this->getOption('max_items', 5)) {
				break;
			}
		}

		$feed_items = $this->processItems($feed_items);

		$tpl = $this->getOption('tpl');
		if (!$tpl) {
			$tpl = 'UserBundle:Portal:feed-' . $this->section;
		}

		$vars = $this->getTplVars();
		$vars = array_merge($vars, array(
			'section'    => $this->section,
			'options'    => $this->options,
			'feed_info'  => $feed_info,
			'feed_items' => $feed_items
		));

		$html = $this->renderView($tpl, $vars);

		return $html;
	}

	public function getTplVars()
	{
		return array();
	}

	protected function processItems(array $feed_items)
	{
		return $feed_items;
	}
}
