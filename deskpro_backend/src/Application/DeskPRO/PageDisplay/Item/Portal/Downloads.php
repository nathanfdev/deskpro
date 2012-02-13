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
 * Renders the downloads browser
 */
class Downloads extends PortalItemAbstract implements CacheableItem
{
	public function getCacheOptions()
	{
		return array('tags' => array('downloads'));
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
			'UserBundle:Downloads:browse',
			array(),
			array('_partial' => 'portal')
		);

		return $html;
	}

	public function getSidebarHtml()
	{
		$category = null;
		if ($this->getOption('category_id')) {
			$category = App::findEntity('DeskPRO:DownloadCategory', $this->getOption('category_id'));
		}

		$downloads = App::getEntityRepository('DeskPRO:Download')->getNewest(
			$this->getValueOption('num_downloads', 5),
			$category
		);

		$html = $this->renderView('UserBundle:Portal:downloads-sidebar.html.twig', array(
			'downloads' => $downloads,
			'title' => $this->getOption('block_title'),
		));

		return $html;
	}

	public function getJsAssets()
	{
		if ($this->section == 'portal') {
			return array('javascripts/DeskPRO/User/ElementHandler/PortalDownloads.js');
		}

		return array();
	}
}