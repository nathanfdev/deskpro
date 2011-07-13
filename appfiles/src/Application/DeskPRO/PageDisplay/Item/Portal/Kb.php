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

/**
 * Renders an AJAXy KB browser in the content section, or renders a simple
 * listing in the sidebar.
 *
 * For the content section, this makes use of partial from `UserBundle:Articles:quickBrowser`
 *
 * @option int  category_id         The initial category in the KB browser, or the category to fetch
 *                                  from in the sidebar (0 for all).
 * @option bool show_cat_switcher   When using content section, show cat switcher?
 */
class Kb extends PortalItemAbstract
{
	public function init()
	{
		if (!$this->hasOption('show_cat_switcher')) {
			$this->setOption('show_cat_switcher', true);
		}
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
			'UserBundle:Articles:browse',
			array(),
			array('_partial' => 'portal')
		);

		return $html;
	}

	public function getSidebarHtml()
	{
		$category = null;
		if ($this->getOption('category_id')) {
			$category = App::findEntity('DeskPRO:ArticleCategory', $this->getOption('category_id'));
		}

		$articles = App::getEntityRepository('DeskPRO:Article')->getNewest(
			$this->getValueOption('num_articles', 10),
			$category
		);

		$html = $this->renderView('UserBundle:Portal:kb-sidebar.html.twig', array(
			'articles' => $articles,
			'block_title' => $this->getOption('block_title'),
		));

		return $html;
	}

	public function getJsAssets()
	{
		if ($this->section == 'portal') {
			return array('javascripts/DeskPRO/User/ElementHandler/PortalKb.js');
		}

		return array();
	}
}