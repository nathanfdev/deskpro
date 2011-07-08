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
 * @option int      category_id         The initial category in the KB browser, or the category to fetch
 *                                      from in the sidebar (0 for all).
 * @option string   status              The initial status in the browser, or the single status to fetch in the sidebar
 * @option bool     show_cat_switcher   When using content section, show cat/status switcher?
 */
class Ideas extends PortalItemAbstract
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
			'UserBundle:Ideas:quickBrowser',
			array('status' => $this->getOption('status', 'new'), 'category_id' => $this->getOption('category_id', 0), 'num' => $this->getValueOption('num_articles', 10)),
			array('_partial' => true)
		);

		$cats = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoryHelper()->getFlatHierarchy();
		$active_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$closed_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();

		$html = $this->renderView('UserBundle:Portal:ideas-content.html.twig', array(
			'html' => $html,
			'cats' => $cats,
			'block_title' => $this->getOption('block_title'),
			'show_cat_switcher' => $this->getOption('show_cat_switcher'),
			'active_status_cats' => $active_status_cats,
			'closed_status_cats' => $closed_status_cats,
		));

		return $html;
	}

	public function getSidebarHtml()
	{
		$category = null;
		if ($this->getOption('category_id')) {
			$category = App::findEntity('DeskPRO:IdeaCategory', $this->getOption('category_id'));
		}

		$ideas = App::getEntityRepository('DeskPRO:Idea')->getNewest(
			$this->getOption('status', 'new'),
			$this->getValueOption('num_articles', 10),
			$category
		);

		$html = $this->renderView('UserBundle:Portal:ideas-sidebar.html.twig', array(
			'ideas' => $ideas,
			'block_title' => $this->getOption('block_title'),
		));

		return $html;
	}

	public function getJsAssets()
	{
		if ($this->section == 'portal') {
			return array('javascripts/DeskPRO/User/ElementHandler/Ideas.js');
		}

		return array();
	}
}