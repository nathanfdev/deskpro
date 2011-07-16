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

use Application\DeskPRO\Entity\PortalPageDisplay;

use Application\DeskPRO\App;

class News extends PortalItemAbstract implements CacheableItem
{
	public function getCacheOptions()
	{
		return array('tags' => array('news'));
	}
	
	public function getHtml()
	{
		if ($this->section == 'portal') {
			return $this->getContentHtml();
		} else {
			return $this->getSidebarHtml();
		}
	}

	public function getContentHtml()
	{
		$html = $this->renderForward(
			'UserBundle:News:browse',
			array(),
			array('_partial' => 'portal', 'per_page' => $this->getOption('per_page', 2))
		);

		return $html;
	}

	public function getSidebarHtml()
	{
		$category = null;
		if ($this->getOption('category_id')) {
			$category = App::findEntity('DeskPRO:NewsCategory', $this->getOption('category_id'));
		}

		$news_entries = App::getEntityRepository('DeskPRO:News')->getNewest(
			$this->getValueOption('num_articles', 5),
			$category
		);

		$html = $this->renderView('UserBundle:Portal:news-sidebar.html.twig', array(
			'news_entries' => $news_entries,
			'block_title' => $this->getOption('block_title'),
		));

		return $html;
	}

	public function getJsAssets()
	{
		if (0 and $this->section == 'portal') {
			return array('javascripts/DeskPRO/User/ElementHandler/PortalNews.js');
		}

		return array();
	}
}