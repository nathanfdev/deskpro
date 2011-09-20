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
class Staff extends PortalItemAbstract implements CacheableItem
{
	public function getCacheOptions()
	{
		return array(
			'lifetime' => 1800, // 30 mins
			'user_indifferent' => true
		);
	}

	public function getHtml()
	{
		if ($this->section == 'sidebar') {
			return $this->getSidebarHtml();
		}

		return '';
	}

	public function getSidebarHtml()
	{
		if ($this->getOption('online')) {
			$staff = App::getEntityRepository('DeskPRO:Person')->getActiveAgents();
		} else {
			$staff = App::getEntityRepository('DeskPRO:Person')->getAgents();
		}

		$html = $this->renderView('UserBundle:Portal:staff-sidebar.html.twig', array(
			'staff' => $staff,
			'title' => $this->getOption('title'),
		));

		return $html;
	}
}
