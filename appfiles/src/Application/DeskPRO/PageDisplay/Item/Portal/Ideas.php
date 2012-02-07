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
class Ideas extends PortalItemAbstract implements CacheableItem
{
	public function getCacheOptions()
	{
		$opt = array(
			'tags' => array('feedback')
		);

		if ($this->section == 'sidebar') {
			$opt['lifetime'] = 1800;
		}

		return $opt;
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
			'UserBundle:Ideas:filter',
			array('status' => $this->getOption('status', 'new'), 'slug' => ''),
			array('_partial' => 'portal')
		);

		return $html;
	}

	public function getSidebarHtml()
	{
		$category = null;
		if ($this->getOption('category_id')) {
			$category = App::findEntity('DeskPRO:FeedbackCategory', $this->getOption('category_id'));
		}

		$feedback = App::getEntityRepository('DeskPRO:Idea')->getNewest(
			$this->getOption('status', 'new'),
			$this->getValueOption('num_articles', 5),
			$category
		);

		$html = $this->renderView('UserBundle:Portal:feedback-sidebar.html.twig', array(
			'feedback' => $feedback,
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
