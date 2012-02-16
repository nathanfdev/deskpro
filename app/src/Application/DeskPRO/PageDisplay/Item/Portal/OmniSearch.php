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
 * Similar to Content except this just takes a template name and renders it.
 *
 * @option string  tpl   The template name
 */
class OmniSearch extends PortalItemAbstract
{
	public function getHtml()
	{
		$query = '';
		if ($this->container->has('request')) {
			$request = $this->container->get('request');
			if ($request->attributes->get('_controller') == 'Application\UserBundle\Controller\SearchController::searchAction') {
				$query = $request->query->get('query', '');
			}
		}

		$html = $this->renderView('UserBundle:Portal:omnisearch-topsection.html.twig', array(
			'section' => $this->section,
			'options' => $this->options,
			'query'   => $query
		));

		return $html;
	}

	public function getJsAssets()
	{
		return array(
			'javascripts/DeskPRO/User/ElementHandler/OmniSearch.js'
		);
	}
}