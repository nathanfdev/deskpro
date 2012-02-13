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

/**
 * Renders notifications section at the top of the page if the user
 * is logged in and has notifications.
 */
class Notifications extends PortalItemAbstract
{
	public function getHtml()
	{
		if ($this->person_context->isGuest()) {
			return '';
		}
		
		$html = $this->renderView('UserBundle:Portal:notifications-topsection.html.twig', array(
			'section' => $this->section,
			'options' => $this->options
		));

		return $html;
	}

	public function getJsAssets()
	{
		return array(
			'javascripts/DeskPRO/User/ElementHandler/Notifications.js'
		);
	}
}