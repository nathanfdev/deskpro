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
 * Similar to Content except this just takes a template name and renders it.
 *
 * @option string  tpl   The template name
 */
class OmniSearch extends PortalItemAbstract
{
	public function getHtml()
	{
		$html = $this->controller->renderView('UserBundle:Portal:omnisearch-content.html.twig', array(
			'section' => $this->section,
			'options' => $this->options
		));

		return $html;
	}

	public function getJsAssets()
	{
		return array(
			'javascripts/DeskPRO/User/PortalHandler/OmniSearch.js'
		);
	}
}